<?php

namespace app\admin\libs\module;

use RuntimeException;
use Throwable;
use think\facade\Cache;
use think\facade\Db;
use app\admin\service\ModuleService;
use app\admin\service\RbacService;
use app\common\service\BaseService;

/**
 * 模块菜单同步器
 */
class ModuleMenuSynchronizer extends BaseService
{
    /**
     * 允许自动更新的菜单字段
     */
    private const SAFE_UPDATE_FIELDS = ['name', 'status', 'icon', 'remark', 'listorder'];

    /**
     * 模块目录名称
     *
     * @var string
     */
    private $moduleName;

    /**
     * 构造模块菜单同步器
     *
     * @param string $moduleName 模块目录名称
     */
    public function __construct(string $moduleName)
    {
        $moduleName = strtolower(trim($moduleName));
        if ($moduleName === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $moduleName)) {
            throw new RuntimeException('模块名称格式不正确');
        }
        $this->moduleName = $moduleName;
    }

    /**
     * 分析模块菜单同步计划
     *
     * @return array
     */
    public function analyze(): array
    {
        try {
            $plan = $this->analyzePlan(false);
            if (!empty($plan['items']['error'])) {
                return self::createReturn(false, $plan, $plan['items']['error'][0]['message']);
            }
            return self::createReturn(true, $plan, '菜单同步分析完成');
        } catch (Throwable $e) {
            return self::createReturn(false, null, $e->getMessage());
        }
    }

    /**
     * 执行模块菜单同步
     *
     * @param string $expectedFingerprint 预览阶段计划指纹
     * @return array
     */
    public function sync(string $expectedFingerprint): array
    {
        if ($expectedFingerprint === '') {
            return self::createReturn(false, null, '缺少菜单同步计划指纹');
        }

        Db::startTrans();
        try {
            $plan = $this->analyzePlan(true);
            if (!empty($plan['items']['error'])) {
                Db::rollback();
                return self::createReturn(false, $plan, $plan['items']['error'][0]['message']);
            }
            if (!hash_equals($expectedFingerprint, $plan['fingerprint'])) {
                Db::rollback();
                $plan['reason'] = 'fingerprint_changed';
                return self::createReturn(false, $plan, '菜单数据或配置已变化，请重新预览后执行');
            }

            $result = $this->applyPlan($plan);
            Db::commit();
        } catch (Throwable $e) {
            Db::rollback();
            return self::createReturn(false, [
                'data_committed' => false,
            ], '菜单同步失败：'.$e->getMessage());
        }

        $result['data_committed'] = true;
        $result['cache_cleared'] = false;
        try {
            if (!Cache::tag(RbacService::CacheTagName)->clear()) {
                return self::createReturn(false, $result, '菜单已写入，但 RBAC 缓存清理失败，请人工清理缓存');
            }
            $result['cache_cleared'] = true;
        } catch (Throwable $e) {
            $result['cache_error'] = $e->getMessage();
            return self::createReturn(false, $result, '菜单已写入，但 RBAC 缓存清理失败，请人工清理缓存');
        }

        return self::createReturn(true, $result, '菜单同步完成');
    }

    /**
     * 构建模块菜单同步计划
     *
     * @param bool $lock 是否锁定相关菜单记录
     * @return array
     */
    private function analyzePlan(bool $lock): array
    {
        $this->validateModule();
        $menuConfig = $this->loadMenuConfig();
        $configNodes = $this->normalizeMenuTree($menuConfig);
        $parentResult = $this->resolveRootParentMenus($configNodes, $lock);
        $configNodes = $parentResult['nodes'];
        $this->validateConfig($configNodes);

        $databaseMenus = $this->getModuleMenus($lock);
        $plan = $this->buildSyncPlan($configNodes, $databaseMenus);
        $configSnapshot = $this->buildConfigSnapshot($configNodes);
        $databaseSnapshot = $this->buildDatabaseSnapshot(array_merge(
            $databaseMenus,
            $parentResult['menus']
        ));

        $plan['module'] = $this->moduleName;
        $plan['config_snapshot'] = $configSnapshot;
        $plan['database_snapshot'] = $databaseSnapshot;
        $plan['fingerprint'] = $this->buildFingerprint($configSnapshot, $databaseSnapshot);

        return $plan;
    }

    /**
     * 校验模块是否允许同步
     *
     * @return void
     */
    private function validateModule(): void
    {
        if (in_array($this->moduleName, ModuleService::SystemModuleList, true)) {
            throw new RuntimeException("系统模块 {$this->moduleName} 不支持菜单同步");
        }

        $modulePath = base_path().$this->moduleName;
        if (!is_dir($modulePath) || !is_file($modulePath.'/Config.inc.php')) {
            throw new RuntimeException("模块 {$this->moduleName} 不存在");
        }

        $installed = Db::name('module')
            ->whereRaw('LOWER(`module`) = ?', [$this->moduleName])
            ->count();
        if ($installed <= 0) {
            throw new RuntimeException("模块 {$this->moduleName} 尚未安装");
        }
    }

    /**
     * 加载模块菜单配置
     *
     * @return array
     */
    private function loadMenuConfig(): array
    {
        $menuFile = base_path().$this->moduleName.'/install/Menu.php';
        if (!is_file($menuFile)) {
            throw new RuntimeException("模块 {$this->moduleName} 不存在 install/Menu.php");
        }

        $menuConfig = include $menuFile;
        if (!is_array($menuConfig)) {
            throw new RuntimeException("模块 {$this->moduleName} 的 Menu.php 必须返回数组");
        }
        return $menuConfig;
    }

    /**
     * 标准化菜单配置树
     *
     * @param array $menus 菜单配置树
     * @param string|null $parentRef 父配置节点引用
     * @param string $indexPrefix 节点索引前缀
     * @return array
     */
    private function normalizeMenuTree(array $menus, ?string $parentRef = null, string $indexPrefix = ''): array
    {
        $nodes = [];
        $sequence = 0;
        $this->appendNormalizedNodes($menus, $parentRef, $indexPrefix, '', 0, $sequence, $nodes);
        return $nodes;
    }

    /**
     * 递归追加标准化菜单节点
     *
     * @param array $menus 菜单配置树
     * @param string|null $parentRef 父配置节点引用
     * @param string $indexPrefix 节点索引前缀
     * @param string $parentPath 父菜单路径
     * @param int $depth 节点深度
     * @param int $sequence 遍历顺序
     * @param array $nodes 标准化节点
     * @return void
     */
    private function appendNormalizedNodes(
        array $menus,
        ?string $parentRef,
        string $indexPrefix,
        string $parentPath,
        int $depth,
        int &$sequence,
        array &$nodes
    ): void {
        foreach ($menus as $index => $menu) {
            if (!is_array($menu)) {
                throw new RuntimeException('菜单配置节点必须为数组');
            }

            $nodeRef = $indexPrefix === '' ? (string) $index : $indexPrefix.'.'.$index;
            $name = isset($menu['name']) && is_scalar($menu['name']) ? trim((string) $menu['name']) : '';
            if ($name === '') {
                throw new RuntimeException("菜单节点 {$nodeRef} 缺少 name");
            }
            $routeText = isset($menu['route']) && is_scalar($menu['route']) ? trim((string) $menu['route']) : '';
            if ($routeText === '') {
                throw new RuntimeException("菜单 {$name} 缺少 route");
            }

            $route = $this->parseRoute($routeText);
            $path = $parentPath === '' ? $name : $parentPath.'/'.$name;
            $type = $this->normalizeInteger($menu['type'] ?? 1, 'type', $path);
            $status = $this->normalizeInteger($menu['status'] ?? 0, 'status', $path);
            if (!in_array($type, [0, 1], true)) {
                throw new RuntimeException("菜单 {$path} 的 type 只能为 0 或 1");
            }
            if (!in_array($status, [0, 1], true)) {
                throw new RuntimeException("菜单 {$path} 的 status 只能为 0 或 1");
            }

            $configuredParentId = null;
            if ($parentRef === null && array_key_exists('parentid', $menu) && $menu['parentid'] !== null) {
                $configuredParentId = $this->normalizeInteger($menu['parentid'], 'parentid', $path);
                if ($configuredParentId < 0) {
                    throw new RuntimeException("菜单 {$path} 的 parentid 不能小于 0");
                }
            }

            $child = $menu['child'] ?? [];
            if (!is_array($child)) {
                throw new RuntimeException("菜单 {$path} 的 child 必须为数组");
            }

            $node = [
                'node_ref' => $nodeRef,
                'parent_ref' => $parentRef,
                'path' => $path,
                'parent_path' => $parentPath,
                'configured_parentid' => $configuredParentId,
                'target_parent_id' => null,
                'declared_fields' => array_keys($menu),
                'sequence' => $sequence++,
                'depth' => $depth,
                'route' => $routeText,
                'app' => $route['app'],
                'controller' => $route['controller'],
                'action' => $route['action'],
                'parameter' => $this->normalizeText($menu['parameter'] ?? '', 'parameter', $path),
                'type' => $type,
                'status' => $status,
                'name' => $name,
                'remark' => $this->normalizeText($menu['remark'] ?? '', 'remark', $path),
                'listorder' => $this->normalizeInteger($menu['listorder'] ?? 0, 'listorder', $path),
                'icon' => $this->normalizeText($menu['icon'] ?? '', 'icon', $path),
            ];
            if ($node['listorder'] < 0) {
                throw new RuntimeException("菜单 {$path} 的 listorder 不能小于 0");
            }
            $nodes[$nodeRef] = $node;

            if (!empty($child)) {
                $this->appendNormalizedNodes(
                    $child,
                    $nodeRef,
                    $nodeRef,
                    $path,
                    $depth + 1,
                    $sequence,
                    $nodes
                );
            }
        }
    }

    /**
     * 解析菜单路由
     *
     * @param string $route 菜单路由
     * @return array
     */
    private function parseRoute(string $route): array
    {
        $parts = explode('/', $route, 3);
        if (count($parts) === 2) {
            array_unshift($parts, $parts[0]);
        }
        if (count($parts) !== 3 || $parts[0] === '' || $parts[1] === '' || $parts[2] === '') {
            throw new RuntimeException("菜单路由 {$route} 无法解析");
        }
        return [
            'app' => $parts[0],
            'controller' => $parts[1],
            'action' => $parts[2],
        ];
    }

    /**
     * 标准化整数字段
     *
     * @param mixed $value 字段值
     * @param string $field 字段名称
     * @param string $path 菜单路径
     * @return int
     */
    private function normalizeInteger($value, string $field, string $path): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^-?\d+$/', $value)) {
            return (int) $value;
        }
        throw new RuntimeException("菜单 {$path} 的 {$field} 必须为整数");
    }

    /**
     * 标准化文本字段
     *
     * @param mixed $value 字段值
     * @param string $field 字段名称
     * @param string $path 菜单路径
     * @return string
     */
    private function normalizeText($value, string $field, string $path): string
    {
        if (!is_scalar($value) && $value !== null) {
            throw new RuntimeException("菜单 {$path} 的 {$field} 必须为文本");
        }
        return (string) $value;
    }

    /**
     * 解析顶层父菜单
     *
     * @param array $nodes 标准化配置节点
     * @param bool $lock 是否锁定菜单记录
     * @return array
     */
    private function resolveRootParentMenus(array $nodes, bool $lock): array
    {
        $parentMenus = [];
        $defaultMenu = null;
        foreach ($nodes as $nodeRef => $node) {
            if ($node['parent_ref'] !== null) {
                continue;
            }

            $parentId = $node['configured_parentid'];
            if ($parentId === null) {
                if ($defaultMenu === null) {
                    $defaultMenu = $this->findDefaultParentMenu($lock);
                    $parentMenus[$defaultMenu['id']] = $defaultMenu;
                }
                $parentId = $defaultMenu['id'];
            } elseif ($parentId > 0) {
                $parentMenu = $this->findMenuById($parentId, $lock);
                $parentMenus[$parentMenu['id']] = $parentMenu;
            }
            $nodes[$nodeRef]['target_parent_id'] = (int) $parentId;
        }
        return [
            'nodes' => $nodes,
            'menus' => array_values($parentMenus),
        ];
    }

    /**
     * 查询系统模块父菜单
     *
     * @param bool $lock 是否锁定菜单记录
     * @return array
     */
    private function findDefaultParentMenu(bool $lock): array
    {
        $query = Db::name('menu')
            ->whereRaw('LOWER(`app`) = ?', ['admin'])
            ->whereRaw('LOWER(`controller`) = ?', ['module'])
            ->whereRaw('LOWER(`action`) = ?', ['index'])
            ->where('type', '=', 0)
            ->order('id', 'ASC');
        if ($lock) {
            $query->lock(true);
        }
        $menus = $query->select()->toArray();
        if (count($menus) !== 1) {
            throw new RuntimeException('无法唯一定位系统“模块”父菜单 admin/Module/index');
        }
        return $this->normalizeDatabaseMenu($menus[0]);
    }

    /**
     * 按 ID 查询配置指定的父菜单
     *
     * @param int $parentId 父菜单 ID
     * @param bool $lock 是否锁定菜单记录
     * @return array
     */
    private function findMenuById(int $parentId, bool $lock): array
    {
        $query = Db::name('menu')->where('id', '=', $parentId);
        if ($lock) {
            $query->lock(true);
        }
        $menu = $query->find();
        if (empty($menu)) {
            throw new RuntimeException("配置指定的父菜单 ID {$parentId} 不存在");
        }
        return $this->normalizeDatabaseMenu($menu);
    }

    /**
     * 校验标准化菜单配置
     *
     * @param array $nodes 标准化配置节点
     * @return void
     */
    private function validateConfig(array $nodes): void
    {
        $exactKeys = [];
        foreach ($nodes as $node) {
            if (strtolower($node['app']) !== $this->moduleName) {
                throw new RuntimeException("菜单 {$node['path']} 声明了跨模块路由 {$node['route']}");
            }
            $parentKey = $node['parent_ref'] === null
                ? 'db:'.$node['target_parent_id']
                : 'node:'.$node['parent_ref'];
            $key = $parentKey.'|'.$this->exactKey($node);
            if (isset($exactKeys[$key])) {
                throw new RuntimeException(
                    "菜单 {$node['path']} 与 {$exactKeys[$key]} 是无法区分的同级重复节点"
                );
            }
            $exactKeys[$key] = $node['path'];
        }
    }

    /**
     * 读取模块数据库菜单
     *
     * @param bool $lock 是否锁定菜单记录
     * @return array
     */
    private function getModuleMenus(bool $lock): array
    {
        $query = Db::name('menu')
            ->whereRaw('LOWER(`app`) = ?', [$this->moduleName])
            ->order('id', 'ASC');
        if ($lock) {
            $query->lock(true);
        }
        $menus = $query->select()->toArray();
        return array_map(function ($menu) {
            return $this->normalizeDatabaseMenu($menu);
        }, $menus);
    }

    /**
     * 标准化数据库菜单
     *
     * @param array $menu 数据库菜单
     * @return array
     */
    private function normalizeDatabaseMenu(array $menu): array
    {
        return [
            'id' => (int) $menu['id'],
            'name' => (string) $menu['name'],
            'parentid' => (int) $menu['parentid'],
            'app' => (string) $menu['app'],
            'controller' => (string) $menu['controller'],
            'action' => (string) $menu['action'],
            'parameter' => (string) $menu['parameter'],
            'type' => (int) $menu['type'],
            'status' => (int) $menu['status'],
            'remark' => (string) $menu['remark'],
            'listorder' => (int) $menu['listorder'],
            'icon' => (string) $menu['icon'],
        ];
    }

    /**
     * 构建同步计划
     *
     * @param array $configNodes 配置菜单节点
     * @param array $databaseMenus 数据库菜单
     * @return array
     */
    private function buildSyncPlan(array $configNodes, array $databaseMenus): array
    {
        $plannedNodes = [];
        foreach ($configNodes as $nodeRef => $node) {
            $node['menu_id'] = null;
            $node['operation'] = null;
            $node['changes'] = [];
            $plannedNodes[$nodeRef] = $node;
        }

        $databaseById = [];
        foreach ($databaseMenus as $menu) {
            $databaseById[$menu['id']] = $menu;
        }

        $matchedIds = [];
        $blockedRefs = [];
        $blockedIds = [];
        $errors = [];
        $processedGroups = [];

        do {
            $progress = $this->matchAvailableSiblingGroups(
                $plannedNodes,
                $databaseById,
                $matchedIds,
                $blockedRefs,
                $blockedIds,
                $processedGroups,
                $errors
            );
            if ($this->matchGlobalUnique(
                $plannedNodes,
                $databaseById,
                $matchedIds,
                $blockedRefs,
                $blockedIds,
                true
            )) {
                $progress = true;
            }
            if (!$progress && $this->matchGlobalUnique(
                $plannedNodes,
                $databaseById,
                $matchedIds,
                $blockedRefs,
                $blockedIds,
                false
            )) {
                $progress = true;
            }
        } while ($progress);

        $this->markRemainingAmbiguities(
            $plannedNodes,
            $databaseById,
            $matchedIds,
            $blockedRefs,
            $blockedIds,
            $errors
        );

        $items = [
            'add' => [],
            'update' => [],
            'move' => [],
            'stale' => [],
            'unchanged' => [],
            'error' => $errors,
        ];

        foreach ($plannedNodes as $nodeRef => &$node) {
            if (isset($blockedRefs[$nodeRef])) {
                $node['operation'] = 'error';
                continue;
            }
            if ($node['menu_id'] === null) {
                $node['operation'] = 'add';
                $items['add'][] = $this->buildPlanItem($node, null, [
                    'menu' => ['old' => null, 'new' => '新增'],
                ]);
                continue;
            }

            $menu = $databaseById[$node['menu_id']];
            $targetParent = $this->getTargetParentForPlan($node, $plannedNodes);
            $changes = $this->buildSafeUpdates($node, $menu);
            $isMove = $targetParent['id'] === null || $menu['parentid'] !== $targetParent['id'];
            if ($isMove) {
                $changes['parentid'] = [
                    'old' => $menu['parentid'],
                    'new' => $targetParent['id'] === null ? 'node:'.$targetParent['ref'] : $targetParent['id'],
                ];
                $node['operation'] = 'move';
                $node['changes'] = $changes;
                $items['move'][] = $this->buildPlanItem($node, $menu, $changes);
            } elseif (!empty($changes)) {
                $node['operation'] = 'update';
                $node['changes'] = $changes;
                $items['update'][] = $this->buildPlanItem($node, $menu, $changes);
            } else {
                $node['operation'] = 'unchanged';
                $items['unchanged'][] = $this->buildPlanItem($node, $menu, []);
            }
        }
        unset($node);

        foreach ($databaseById as $menuId => $menu) {
            if (isset($matchedIds[$menuId]) || isset($blockedIds[$menuId])) {
                continue;
            }
            $items['stale'][] = [
                'menu_id' => $menuId,
                'path' => $this->buildDatabaseMenuPath($menuId, $databaseById),
                'route' => $this->routeText($menu),
                'changes' => [],
                'message' => '数据库存在但 Menu.php 未匹配，仅报告不删除',
            ];
        }

        $this->detectUnsupportedIdentityChanges($plannedNodes, $databaseById, $items);
        $summary = [];
        foreach ($items as $type => $typeItems) {
            $summary[$type] = count($typeItems);
        }
        $summary['executable'] = $summary['add'] + $summary['update'] + $summary['move'];
        $summary['added_permission_nodes'] = count(array_filter($items['add'], function ($item) use ($plannedNodes) {
            return isset($plannedNodes[$item['node_ref']]) && $plannedNodes[$item['node_ref']]['type'] === 1;
        }));

        return [
            'nodes' => $plannedNodes,
            'items' => $items,
            'summary' => $summary,
        ];
    }

    /**
     * 匹配当前可解析父节点的同级菜单
     *
     * @param array $plannedNodes 计划节点
     * @param array $databaseById 数据库菜单映射
     * @param array $matchedIds 已匹配菜单 ID
     * @param array $blockedRefs 已阻断配置节点
     * @param array $blockedIds 已阻断数据库节点
     * @param array $processedGroups 已处理父级分组
     * @param array $errors 错误列表
     * @return bool
     */
    private function matchAvailableSiblingGroups(
        array &$plannedNodes,
        array $databaseById,
        array &$matchedIds,
        array &$blockedRefs,
        array &$blockedIds,
        array &$processedGroups,
        array &$errors
    ): bool {
        $groups = [];
        foreach ($plannedNodes as $nodeRef => $node) {
            if ($node['parent_ref'] === null) {
                $groupKey = 'db:'.$node['target_parent_id'];
                $parentId = $node['target_parent_id'];
            } else {
                $groupKey = 'node:'.$node['parent_ref'];
                $parentId = $plannedNodes[$node['parent_ref']]['menu_id'];
            }
            if ($parentId === null || isset($processedGroups[$groupKey])) {
                continue;
            }
            $groups[$groupKey]['parent_id'] = (int) $parentId;
            $groups[$groupKey]['refs'][] = $nodeRef;
        }

        $progress = false;
        foreach ($groups as $groupKey => $group) {
            $processedGroups[$groupKey] = true;
            if ($this->matchSiblingNodes(
                $group['refs'],
                $group['parent_id'],
                $plannedNodes,
                $databaseById,
                $matchedIds,
                $blockedRefs,
                $blockedIds,
                $errors
            )) {
                $progress = true;
            }
        }
        return $progress;
    }

    /**
     * 批量匹配同一父级下的菜单
     *
     * @param array $nodeRefs 配置节点引用
     * @param int $parentId 数据库父菜单 ID
     * @param array $plannedNodes 计划节点
     * @param array $databaseById 数据库菜单映射
     * @param array $matchedIds 已匹配菜单 ID
     * @param array $blockedRefs 已阻断配置节点
     * @param array $blockedIds 已阻断数据库节点
     * @param array $errors 错误列表
     * @return bool
     */
    private function matchSiblingNodes(
        array $nodeRefs,
        int $parentId,
        array &$plannedNodes,
        array $databaseById,
        array &$matchedIds,
        array &$blockedRefs,
        array &$blockedIds,
        array &$errors
    ): bool {
        $candidateIds = [];
        foreach ($databaseById as $menuId => $menu) {
            if ($menu['parentid'] === $parentId && !isset($matchedIds[$menuId]) && !isset($blockedIds[$menuId])) {
                $candidateIds[] = $menuId;
            }
        }

        $progress = $this->matchBuckets(
            $nodeRefs,
            $candidateIds,
            $plannedNodes,
            $databaseById,
            $matchedIds,
            $blockedRefs,
            $blockedIds,
            $errors,
            true,
            true
        );

        $remainingRefs = $this->filterUnmatchedRefs($nodeRefs, $plannedNodes, $blockedRefs);
        $remainingIds = $this->filterUnmatchedIds($candidateIds, $matchedIds, $blockedIds);
        if ($this->matchBuckets(
            $remainingRefs,
            $remainingIds,
            $plannedNodes,
            $databaseById,
            $matchedIds,
            $blockedRefs,
            $blockedIds,
            $errors,
            false,
            true
        )) {
            $progress = true;
        }
        return $progress;
    }

    /**
     * 按键分桶匹配菜单
     *
     * @param array $nodeRefs 配置节点引用
     * @param array $menuIds 数据库菜单 ID
     * @param array $plannedNodes 计划节点
     * @param array $databaseById 数据库菜单映射
     * @param array $matchedIds 已匹配菜单 ID
     * @param array $blockedRefs 已阻断配置节点
     * @param array $blockedIds 已阻断数据库节点
     * @param array $errors 错误列表
     * @param bool $exact 是否包含名称匹配
     * @param bool $blockAmbiguous 是否阻断歧义桶
     * @return bool
     */
    private function matchBuckets(
        array $nodeRefs,
        array $menuIds,
        array &$plannedNodes,
        array $databaseById,
        array &$matchedIds,
        array &$blockedRefs,
        array &$blockedIds,
        array &$errors,
        bool $exact,
        bool $blockAmbiguous
    ): bool {
        $configBuckets = [];
        foreach ($nodeRefs as $nodeRef) {
            if ($plannedNodes[$nodeRef]['menu_id'] !== null || isset($blockedRefs[$nodeRef])) {
                continue;
            }
            $key = $exact ? $this->exactKey($plannedNodes[$nodeRef]) : $this->identityKey($plannedNodes[$nodeRef]);
            $configBuckets[$key][] = $nodeRef;
        }

        $databaseBuckets = [];
        foreach ($menuIds as $menuId) {
            if (isset($matchedIds[$menuId]) || isset($blockedIds[$menuId])) {
                continue;
            }
            $key = $exact ? $this->exactKey($databaseById[$menuId]) : $this->identityKey($databaseById[$menuId]);
            $databaseBuckets[$key][] = $menuId;
        }

        $progress = false;
        foreach ($configBuckets as $key => $refs) {
            $ids = $databaseBuckets[$key] ?? [];
            if (count($refs) === 1 && count($ids) === 1) {
                $this->assignMatch($refs[0], $ids[0], $plannedNodes, $matchedIds);
                $progress = true;
                continue;
            }
            if ($blockAmbiguous && !empty($ids)) {
                $this->blockAmbiguousBucket(
                    $refs,
                    $ids,
                    $plannedNodes,
                    $blockedRefs,
                    $blockedIds,
                    $errors
                );
            }
        }
        return $progress;
    }

    /**
     * 全模块匹配唯一移动候选
     *
     * @param array $plannedNodes 计划节点
     * @param array $databaseById 数据库菜单映射
     * @param array $matchedIds 已匹配菜单 ID
     * @param array $blockedRefs 已阻断配置节点
     * @param array $blockedIds 已阻断数据库节点
     * @param bool $exact 是否包含名称匹配
     * @return bool
     */
    private function matchGlobalUnique(
        array &$plannedNodes,
        array $databaseById,
        array &$matchedIds,
        array $blockedRefs,
        array $blockedIds,
        bool $exact
    ): bool {
        $nodeRefs = $this->filterUnmatchedRefs(array_keys($plannedNodes), $plannedNodes, $blockedRefs);
        $menuIds = $this->filterUnmatchedIds(array_keys($databaseById), $matchedIds, $blockedIds);
        $unusedErrors = [];
        $unusedBlockedRefs = $blockedRefs;
        $unusedBlockedIds = $blockedIds;
        return $this->matchBuckets(
            $nodeRefs,
            $menuIds,
            $plannedNodes,
            $databaseById,
            $matchedIds,
            $unusedBlockedRefs,
            $unusedBlockedIds,
            $unusedErrors,
            $exact,
            false
        );
    }

    /**
     * 标记全模块剩余歧义
     *
     * @param array $plannedNodes 计划节点
     * @param array $databaseById 数据库菜单映射
     * @param array $matchedIds 已匹配菜单 ID
     * @param array $blockedRefs 已阻断配置节点
     * @param array $blockedIds 已阻断数据库节点
     * @param array $errors 错误列表
     * @return void
     */
    private function markRemainingAmbiguities(
        array &$plannedNodes,
        array $databaseById,
        array &$matchedIds,
        array &$blockedRefs,
        array &$blockedIds,
        array &$errors
    ): void {
        $nodeRefs = $this->filterUnmatchedRefs(array_keys($plannedNodes), $plannedNodes, $blockedRefs);
        $menuIds = $this->filterUnmatchedIds(array_keys($databaseById), $matchedIds, $blockedIds);
        $this->matchBuckets(
            $nodeRefs,
            $menuIds,
            $plannedNodes,
            $databaseById,
            $matchedIds,
            $blockedRefs,
            $blockedIds,
            $errors,
            true,
            true
        );

        $nodeRefs = $this->filterUnmatchedRefs(array_keys($plannedNodes), $plannedNodes, $blockedRefs);
        $menuIds = $this->filterUnmatchedIds(array_keys($databaseById), $matchedIds, $blockedIds);
        $this->matchBuckets(
            $nodeRefs,
            $menuIds,
            $plannedNodes,
            $databaseById,
            $matchedIds,
            $blockedRefs,
            $blockedIds,
            $errors,
            false,
            true
        );
    }

    /**
     * 记录一组无法唯一匹配的菜单
     *
     * @param array $refs 配置节点引用
     * @param array $ids 数据库菜单 ID
     * @param array $plannedNodes 计划节点
     * @param array $blockedRefs 已阻断配置节点
     * @param array $blockedIds 已阻断数据库节点
     * @param array $errors 错误列表
     * @return void
     */
    private function blockAmbiguousBucket(
        array $refs,
        array $ids,
        array $plannedNodes,
        array &$blockedRefs,
        array &$blockedIds,
        array &$errors
    ): void {
        foreach ($refs as $ref) {
            $blockedRefs[$ref] = true;
        }
        foreach ($ids as $id) {
            $blockedIds[$id] = true;
        }
        $paths = array_map(function ($ref) use ($plannedNodes) {
            return $plannedNodes[$ref]['path'];
        }, $refs);
        $errors[] = [
            'menu_id' => implode(',', $ids),
            'node_ref' => implode(',', $refs),
            'path' => implode('、', $paths),
            'route' => isset($plannedNodes[$refs[0]]) ? $plannedNodes[$refs[0]]['route'] : '',
            'changes' => [],
            'message' => '菜单无法唯一匹配，候选 ID：'.implode(',', $ids),
        ];
    }

    /**
     * 绑定配置节点和数据库菜单
     *
     * @param string $nodeRef 配置节点引用
     * @param int $menuId 数据库菜单 ID
     * @param array $plannedNodes 计划节点
     * @param array $matchedIds 已匹配菜单 ID
     * @return void
     */
    private function assignMatch(string $nodeRef, int $menuId, array &$plannedNodes, array &$matchedIds): void
    {
        $plannedNodes[$nodeRef]['menu_id'] = $menuId;
        $matchedIds[$menuId] = true;
    }

    /**
     * 筛选未匹配配置节点
     *
     * @param array $nodeRefs 配置节点引用
     * @param array $plannedNodes 计划节点
     * @param array $blockedRefs 已阻断配置节点
     * @return array
     */
    private function filterUnmatchedRefs(array $nodeRefs, array $plannedNodes, array $blockedRefs): array
    {
        return array_values(array_filter($nodeRefs, function ($nodeRef) use ($plannedNodes, $blockedRefs) {
            return $plannedNodes[$nodeRef]['menu_id'] === null && !isset($blockedRefs[$nodeRef]);
        }));
    }

    /**
     * 筛选未匹配数据库菜单
     *
     * @param array $menuIds 数据库菜单 ID
     * @param array $matchedIds 已匹配菜单 ID
     * @param array $blockedIds 已阻断数据库节点
     * @return array
     */
    private function filterUnmatchedIds(array $menuIds, array $matchedIds, array $blockedIds): array
    {
        return array_values(array_filter($menuIds, function ($menuId) use ($matchedIds, $blockedIds) {
            return !isset($matchedIds[$menuId]) && !isset($blockedIds[$menuId]);
        }));
    }

    /**
     * 获取计划节点目标父级
     *
     * @param array $node 当前配置节点
     * @param array $plannedNodes 全部计划节点
     * @return array
     */
    private function getTargetParentForPlan(array $node, array $plannedNodes): array
    {
        if ($node['parent_ref'] === null) {
            return ['id' => (int) $node['target_parent_id'], 'ref' => null];
        }
        $parentNode = $plannedNodes[$node['parent_ref']];
        return [
            'id' => $parentNode['menu_id'] === null ? null : (int) $parentNode['menu_id'],
            'ref' => $node['parent_ref'],
        ];
    }

    /**
     * 计算已有菜单安全字段差异
     *
     * @param array $node 配置节点
     * @param array $menu 数据库菜单
     * @return array
     */
    private function buildSafeUpdates(array $node, array $menu): array
    {
        $changes = [];
        foreach (self::SAFE_UPDATE_FIELDS as $field) {
            if ($field === 'listorder' && !in_array('listorder', $node['declared_fields'], true)) {
                continue;
            }
            $oldValue = $menu[$field];
            $newValue = $node[$field];
            if (in_array($field, ['status', 'listorder'], true)) {
                $oldValue = (int) $oldValue;
                $newValue = (int) $newValue;
            } else {
                $oldValue = (string) $oldValue;
                $newValue = (string) $newValue;
            }
            if ($oldValue !== $newValue) {
                $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
            }
        }
        return $changes;
    }

    /**
     * 构建命令展示计划项
     *
     * @param array $node 配置节点
     * @param array|null $menu 数据库菜单
     * @param array $changes 字段变化
     * @return array
     */
    private function buildPlanItem(array $node, ?array $menu, array $changes): array
    {
        return [
            'menu_id' => $menu['id'] ?? null,
            'node_ref' => $node['node_ref'],
            'path' => $node['path'],
            'route' => $node['route'],
            'changes' => $changes,
            'message' => '',
        ];
    }

    /**
     * 检测不支持的身份字段变化
     *
     * @param array $plannedNodes 计划节点
     * @param array $databaseById 数据库菜单映射
     * @param array $items 计划分类
     * @return void
     */
    private function detectUnsupportedIdentityChanges(array &$plannedNodes, array $databaseById, array &$items): void
    {
        foreach ($items['add'] as $addIndex => $addItem) {
            $node = $plannedNodes[$addItem['node_ref']];
            $targetParent = $this->getTargetParentForPlan($node, $plannedNodes);
            if ($targetParent['id'] === null) {
                continue;
            }
            foreach ($items['stale'] as $staleIndex => $staleItem) {
                $staleMenu = $databaseById[$staleItem['menu_id']];
                if ($staleMenu['parentid'] !== $targetParent['id'] || $staleMenu['name'] !== $node['name']) {
                    continue;
                }
                if ($this->identityKey($staleMenu) === $this->identityKey($node)) {
                    continue;
                }
                $message = "菜单 {$node['path']} 修改了 route、type 或 parameter，请使用数据库迁移处理";
                $items['error'][] = [
                    'menu_id' => $staleMenu['id'],
                    'node_ref' => $node['node_ref'],
                    'path' => $node['path'],
                    'route' => $node['route'],
                    'changes' => [
                        'identity' => [
                            'old' => $this->routeText($staleMenu).' type='.$staleMenu['type'].' parameter='.$staleMenu['parameter'],
                            'new' => $node['route'].' type='.$node['type'].' parameter='.$node['parameter'],
                        ],
                    ],
                    'message' => $message,
                ];
                $plannedNodes[$node['node_ref']]['operation'] = 'error';
                unset($items['add'][$addIndex], $items['stale'][$staleIndex]);
                break;
            }
        }
        $items['add'] = array_values($items['add']);
        $items['stale'] = array_values($items['stale']);
    }

    /**
     * 构建节点基础身份键
     *
     * @param array $node 菜单节点
     * @return string
     */
    private function identityKey(array $node): string
    {
        return implode("\x1F", [
            strtolower((string) $node['app']),
            strtolower((string) $node['controller']),
            strtolower((string) $node['action']),
            (string) $node['parameter'],
            (string) ((int) $node['type']),
        ]);
    }

    /**
     * 构建包含名称的精确身份键
     *
     * @param array $node 菜单节点
     * @return string
     */
    private function exactKey(array $node): string
    {
        return $this->identityKey($node)."\x1F".(string) $node['name'];
    }

    /**
     * 生成可读菜单路由
     *
     * @param array $menu 菜单数据
     * @return string
     */
    private function routeText(array $menu): string
    {
        return $menu['app'].'/'.$menu['controller'].'/'.$menu['action'];
    }

    /**
     * 构建数据库菜单可读路径
     *
     * @param int $menuId 菜单 ID
     * @param array $databaseById 数据库菜单映射
     * @return string
     */
    private function buildDatabaseMenuPath(int $menuId, array $databaseById): string
    {
        $names = [];
        $visited = [];
        while (isset($databaseById[$menuId]) && !isset($visited[$menuId])) {
            $visited[$menuId] = true;
            array_unshift($names, $databaseById[$menuId]['name']);
            $menuId = $databaseById[$menuId]['parentid'];
        }
        return implode('/', $names);
    }

    /**
     * 构建规范化配置快照
     *
     * @param array $nodes 配置菜单节点
     * @return array
     */
    private function buildConfigSnapshot(array $nodes): array
    {
        uasort($nodes, function ($a, $b) {
            return $a['sequence'] <=> $b['sequence'];
        });
        return array_values(array_map(function ($node) {
            return [
                'node_ref' => $node['node_ref'],
                'parent_ref' => $node['parent_ref'],
                'configured_parentid' => $node['configured_parentid'],
                'target_parent_id' => $node['target_parent_id'],
                'declared_fields' => array_values($node['declared_fields']),
                'app' => $node['app'],
                'controller' => $node['controller'],
                'action' => $node['action'],
                'parameter' => $node['parameter'],
                'type' => $node['type'],
                'status' => $node['status'],
                'name' => $node['name'],
                'remark' => $node['remark'],
                'listorder' => $node['listorder'],
                'icon' => $node['icon'],
            ];
        }, $nodes));
    }

    /**
     * 构建规范化数据库快照
     *
     * @param array $menus 数据库菜单
     * @return array
     */
    private function buildDatabaseSnapshot(array $menus): array
    {
        $uniqueMenus = [];
        foreach ($menus as $menu) {
            $menu = $this->normalizeDatabaseMenu($menu);
            $uniqueMenus[$menu['id']] = $menu;
        }
        ksort($uniqueMenus, SORT_NUMERIC);
        return array_values($uniqueMenus);
    }

    /**
     * 生成菜单同步计划指纹
     *
     * @param array $configSnapshot 配置快照
     * @param array $databaseSnapshot 数据库快照
     * @return string
     */
    private function buildFingerprint(array $configSnapshot, array $databaseSnapshot): string
    {
        $json = json_encode([
            'config' => $configSnapshot,
            'database' => $databaseSnapshot,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('菜单同步计划指纹生成失败');
        }
        return hash('sha256', $json);
    }

    /**
     * 应用菜单同步计划
     *
     * @param array $plan 菜单同步计划
     * @return array
     */
    private function applyPlan(array $plan): array
    {
        $nodes = $plan['nodes'];
        uasort($nodes, function ($a, $b) {
            return $a['sequence'] <=> $b['sequence'];
        });
        $nodeIds = [];
        foreach ($nodes as $nodeRef => $node) {
            if ($node['operation'] === 'error') {
                throw new RuntimeException("菜单 {$node['path']} 存在未处理错误");
            }
            $parentId = $node['parent_ref'] === null
                ? (int) $node['target_parent_id']
                : ($nodeIds[$node['parent_ref']] ?? null);
            if ($parentId === null) {
                throw new RuntimeException("菜单 {$node['path']} 无法解析目标父菜单");
            }

            if ($node['operation'] === 'add') {
                $menuId = Db::name('menu')->insertGetId([
                    'name' => $node['name'],
                    'parentid' => $parentId,
                    'app' => $node['app'],
                    'controller' => $node['controller'],
                    'action' => $node['action'],
                    'parameter' => $node['parameter'],
                    'type' => $node['type'],
                    'status' => $node['status'],
                    'remark' => $node['remark'],
                    'listorder' => $node['listorder'],
                    'icon' => $node['icon'],
                ]);
                if (!$menuId) {
                    throw new RuntimeException("菜单 {$node['path']} 新增失败");
                }
                $nodeIds[$nodeRef] = (int) $menuId;
                continue;
            }

            $nodeIds[$nodeRef] = (int) $node['menu_id'];
            if (!in_array($node['operation'], ['update', 'move'], true)) {
                continue;
            }
            $updates = [];
            foreach ($node['changes'] as $field => $change) {
                $updates[$field] = $field === 'parentid' ? $parentId : $change['new'];
            }
            if (!empty($updates)) {
                $updated = Db::name('menu')->where('id', '=', $node['menu_id'])->update($updates);
                if ($updated === false) {
                    throw new RuntimeException("菜单 {$node['path']} 更新失败");
                }
            }
        }

        return [
            'module' => $this->moduleName,
            'summary' => $plan['summary'],
            'fingerprint' => $plan['fingerprint'],
        ];
    }
}
