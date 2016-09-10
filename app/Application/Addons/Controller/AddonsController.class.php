<?php

// +----------------------------------------------------------------------
// |  插件管理
// +----------------------------------------------------------------------

namespace Addons\Controller;

use Common\Controller\AdminBase;

class AddonsController extends AdminBase {

    protected $addons = NULL;

    protected function _initialize() {
        parent::_initialize();
        $this->addons = D('Addons/Addons');
    }

    //显示插件列表
    public function index() {
        $addons = $this->addons->getAddonList();
        if (!empty($addons)) {
            //遍历检查是否有前台
            foreach ($addons as $key => $rs) {
                $path = $this->addons->getAddonsPath() . "{$rs['name']}/Controller/IndexController.class.php";
                if (file_exists($path)) {
                    $addons[$key]['url'] = U("Addons/{$rs['name']}/index");
                } else {
                    $addons[$key]['url'] = '';
                }
            }
        }
        $this->assign('addons', $addons);
        $this->display();
    }

    //安装
    public function install() {
        //插件名称
        $addonName = trim(I('get.addonename'));
        if (empty($addonName)) {
            $this->error('请选择需要安装的插件！');
        }
        if ($this->addons->installAddon($addonName)) {
            $this->success('插件安装成功！', U('Addons/index'));
        } else {
            $error = $this->addons->getError();
            $this->error($error ? $error : '插件安装失败！');
        }
    }

    //卸载插件
    public function uninstall() {
        //插件名称
        $addonId = trim(I('get.id'));
        if (empty($addonId)) {
            $this->error('请选择需要卸载的插件！');
        }
        if ($this->addons->uninstallAddon($addonId)) {
            $this->success('插件卸载成功！', U('Addons/index'));
        } else {
            $error = $this->addons->getError();
            $this->error($error ? $error : '插件卸载失败！');
        }
    }

    //插件设置界面
    public function config() {
        if (IS_POST) {
            //插件ID
            $id = (int) I('post.id');
            //获取插件信息
            $info = $this->addons->where(array('id' => $id))->find();
            if (empty($info)) {
                $this->error('该插件没有安装！');
            }
            //插件配置
            $config = I('post.config', '', '');
            if (false !== $this->addons->where(array('id' => $id))->save(array('config' => serialize($config)))) {
                //更新附件状态，把相关附件和插件进行关联
                service("Attachment")->api_update('', 'addons-' . $id, 1);
                //更新插件缓存
                $this->addons->addons_cache();
                $this->success('保存成功！', U('Addons/index'));
            } else {
                $this->error('保存失败！');
            }
        } else {
            //插件名称
            $addonId = trim(I('get.id'));
            if (empty($addonId)) {
                $this->error('请选择需要操作的插件！');
            }
            //获取插件信息
            $info = $this->addons->where(array('id' => $addonId))->find();
            if (empty($info)) {
                $this->error('该插件没有安装！');
            }
            $info['config'] = unserialize($info['config']);
            //实例化插件入口类
            $addonObj = $this->addons->getAddonObject($info['name']);
            //标题
            $meta_title = '设置插件-' . $addonObj->info['title'];
            //载入插件配置数组
            $fileConfig = include $addonObj->configFile;
            if (!empty($info['config'])) {
                foreach ($fileConfig as $key => $form) {
                    //如果已经有保存的值
                    if (isset($info['config'][$key])) {
                        $fileConfig[$key]['value'] = $info['config'][$key];
                    }
                }
            }
            $this->assign('meta_title', $meta_title);
            $this->assign('config', $fileConfig);
            $this->assign('info', $info);
            $this->display();
        }
    }

    //状态转换
    public function status() {
        //插件名称
        $addonId = trim(I('get.id'));
        if (empty($addonId)) {
            $this->error('请选择需要操作的插件！');
        }
        if ($this->addons->statusAddon($addonId)) {
            $this->success('插件状态成功！', U('Addons/index'));
        } else {
            $error = $this->addons->getError();
            $this->error($error ? $error : '插件状态失败！');
        }
    }

    //创建新插件
    public function create() {
        if (IS_POST) {
            $info = $_POST['info'];
            if (empty($info)) {
                $this->error('插件信息不能为空！');
            }
            //检查必要信息
            $validate = array(
                array('name', 'require', '插件标识不能为空！', 1, 'regex', 3),
                array('name', '/^[a-zA-Z][a-zA-Z0-9_]+$/i', '插件标识只支持英文、数字、下划线！', 0, 'regex', 3),
                array('name', '', '该插件标识已经存在！', 0, 'unique', 1),
                array('sign', 'require', '插件签名不能为空！', 1, 'regex', 3),
                array('sign', '', '该插件签名已经存在！', 0, 'unique', 1),
                array('title', 'require', '插件名称不能为空！', 1, 'regex', 3),
                array('version', 'require', '插件版本不能为空！', 1, 'regex', 3),
                array('author', 'require', '插件作者不能为空！', 1, 'regex', 3),
                array('description', 'require', '插件描述不能为空！', 1, 'regex', 3),
            );
            C('TOKEN_ON', false);
            $data = $this->addons->validate($validate)->create($info, 1);
            if (!$data) {
                $this->error($this->addons->getError());
            }
            //强制插件标识首字母为大写
            $info['name'] = ucwords($info['name']);
            //插件目录
            $addonsDir = $this->addons->getAddonsPath() . "{$info['name']}/";
            //检查插件目录是否存在
            if (file_exists($addonsDir)) {
                $this->error('该插件目录已经存在！');
            }
            //插件是否有后台
            $has_adminlist = (int) $info['has_adminlist'];
            //插件是否支持外部访问
            $has_outurl = $info['has_outurl'];
            //是否需要配置
            $has_config = $info['has_config'];
            //实现行为
            $rule_list = $info['rule_list'];

            //创建插件主文件
            $addonFile = "<?php
/**
 * {$info['title']} 插件
 */

namespace Addon\\{$info['name']};

use \Addons\Util\Addon;

class {$info['name']}Addon extends Addon {

    //插件信息
    public \$info = array(
        'name' => '{$info['name']}',
        'title' => '{$info['title']}',
        'description' => '{$info['description']}',
        'status' => 1,
        'author' => '{$info['author']}',
        'version' => '{$info['version']}',
        'has_adminlist' => {$has_adminlist},
        'sign' => '{$info['sign']}',
    );
";
            if ($has_adminlist) {
                $addonFile .="
    //有开启插件后台情况下，添加对应的控制器方法
    //也就是插件目录下 Action/AdminController.class.php中，public属性的方法！
    //每个方法都是一个数组形式，删除，修改类需要具体参数的，建议隐藏！
    public \$adminlist = array(
        array(
            //方法名称
            \"action\" => \"\",
            //附加参数 例如：a=12&id=777
            \"data\" => \"\",
            //类型，1：权限认证+菜单，0：只作为菜单
            \"type\" => 0,
            //状态，1是显示，0是不显示
            \"status\" => 1,
            //名称
            \"name\" => \"\",
            //备注
            \"remark\" => \"\",
            //排序
            \"listorder\" => 0,
        ),
    );
";
            }
            $addonFile .="
    //安装
    public function install() {
        return true;
    }

    //卸载
    public function uninstall() {
        return true;
    }
";
            //行为
            if ($rule_list) {
                foreach ($rule_list as $key => $behavior) {
                    $addonFile .="
    //实现行为 {$behavior}
    //\$param 是行为传递过来的参数
    public function {$behavior}(\$param = NULL) {
        //具体的处理逻辑代码
    }
";
                }
            }
            $addonFile .="
}
";
            $Dir = new \Dir();
            //创建插件相关目录
            $status = mkdir($addonsDir, 0777, true);
            if ($status == false) {
                $this->error('插件目录创建失败，请检查是否有写权限！');
            }
            //写如插件入口文件
            file_put_contents($addonsDir . "{$info['name']}Addon.class.php", $addonFile);
            //检查入口文件是否正常
            if (!is_file($addonsDir . "{$info['name']}Addon.class.php")) {
                $Dir->delDir($addonsDir);
                $this->error('创建插件失败，插件入口文件没有创建成功！');
            }
            //插件后台
            if ($has_adminlist) {
                $Dir->copyDir(MODULE_PATH . 'Example/Controller/', $addonsDir . 'Controller/');
                $Dir->copyDir(MODULE_PATH . 'Example/View/', $addonsDir . 'View/');
            }
            //插件前台访问
            if ($has_outurl && !$has_adminlist) {
                $Dir->copyDir(MODULE_PATH . 'Example/Controller/', $addonsDir . 'Controller/');
                $Dir->copyDir(MODULE_PATH . 'Example/View/', $addonsDir . 'View/');
                unlink($addonsDir . 'Controller/AdminController.class.php');
                $Dir->delDir($addonsDir . 'View/Admin/');
            } elseif (!$has_outurl && $has_adminlist) {
                unlink($addonsDir . 'Controller/IndexController.class.php');
                $Dir->delDir($addonsDir . 'View/Index/');
            }
            if (is_file($addonsDir . 'Controller/AdminController.class.php')) {
                $AdminController = file_get_contents($addonsDir . 'Controller/AdminController.class.php');
                $AdminController = str_replace('AddonsName', $info['name'], $AdminController);
                file_put_contents($addonsDir . 'Controller/AdminController.class.php', $AdminController);
            }
            if (is_file($addonsDir . 'Controller/IndexController.class.php')) {
                $IndexController = file_get_contents($addonsDir . 'Controller/IndexController.class.php');
                $IndexController = str_replace('AddonsName', $info['name'], $IndexController);
                file_put_contents($addonsDir . 'Controller/IndexController.class.php', $IndexController);
            }
            //插件配置
            if ($has_config) {
                copy(MODULE_PATH . 'Example/Config.php', $addonsDir . 'Config.php');
            }
            $this->success('插件创建成功~', U('Addons/index'));
        } else {
            //查询出系统可用行为
            $behaviorList = M('Behavior')->where(array('status' => 1))->getField('name,title', true);

            $this->assign('behaviorList', $behaviorList);
            $this->display();
        }
    }

    //本地安装
    public function local() {
        if (IS_POST) {
            if (!$_FILES['file']) {
                $this->error("请选择上传文件！");
            }
            //上传临时文件地址
            $filename = $_FILES['file']['tmp_name'];
            if (strtolower(substr($_FILES['file']['name'], -3, 3)) != 'zip') {
                $this->error("上传的文件格式有误！");
            }
            //插件目录
            $addonsDir = $this->addons->getAddonsPath();
            //检查插件目录是否存在
            if (!file_exists($addonsDir)) {
                //创建
                if (mkdir($addonsDir, 0777, true) == false) {
                    $this->error('插件目录' . $addonsDir . '创建失败！');
                }
            }
            //检查插件目录可写权限
            if (is_writeable($addonsDir) === false) {
                $this->error('插件目录' . $addonsDir . '不可写！');
            }
            //插件名称
            $addonName = pathinfo($_FILES['file']['name']);
            $addonName = $addonName['filename'];
            //检查插件目录是否存在
            if (file_exists($addonsDir . $addonName)) {
                $this->error('该插件目录已经存在！');
            }
            //检查必要信息
            $validate = array(
                array('name', 'require', '插件标识不能为空！', 1, 'regex', 3),
                array('name', '/^[a-zA-Z][a-zA-Z0-9_]+$/i', '插件标识只支持英文、数字、下划线！', 0, 'regex', 3),
                array('name', '', '该插件标识已经存在！', 0, 'unique', 1),
            );
            C('TOKEN_ON', false);
            $data = array('name' => $addonName);
            $data = $this->addons->validate($validate)->create($data, 1);
            if (!$data) {
                $this->error($this->addons->getError());
            }
            $zip = new \PclZip($filename);
            $status = $zip->extract(PCLZIP_OPT_PATH, $addonsDir . $addonName);
            if ($status) {
                $this->success('插件解压成功，可以进入插件管理进行安装！', U('Addons/index'));
            } else {
                $this->error('插件解压失败！');
            }
        } else {
            $this->display();
        }
    }

    //打包下载
    public function unpack() {
        $addonName = I('get.addonname');
        if (empty($addonName)) {
            $this->error('请选择需要打包的插件！');
        }
        //插件目录
        $addonsDir = $this->addons->getAddonsPath() . "{$addonName}/";
        $basename = $addonName . '.zip';
        $file = RUNTIME_PATH . $basename;
        //创建压缩包
        $zip = new \PclZip($file);
        $path = explode(':', $addonsDir);
        $zip->create($addonsDir, PCLZIP_OPT_REMOVE_PATH, $path[1] ? $path[1] : $path[0]);

        //获取用户客户端UA，用来处理中文文件名
        $ua = $_SERVER["HTTP_USER_AGENT"];
        //从下载文件地址中获取的后缀
        $fileExt = fileext(basename($file));
        //下载文件名后缀
        $baseNameFileExt = fileext($basename);
        if (preg_match("/MSIE/", $ua)) {
            $filename = iconv("UTF-8", "GB2312//IGNORE", $baseNameFileExt ? $basename : ($basename . "." . $fileExt));
        } else {
            $filename = $baseNameFileExt ? $basename : ($basename . "." . $fileExt);
        }
        header("Content-type: application/octet-stream");
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header("Content-Length: " . filesize($file));
        readfile($file);
    }

}
