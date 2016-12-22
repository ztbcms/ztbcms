<?php

// +----------------------------------------------------------------------
// | 系统行为实现
// +----------------------------------------------------------------------

namespace Think;

class Hook {

	static private $tags = array();

	/**
	 * 动态添加插件到某个标签
	 * @param string $tag 标签名称
	 * @param mixed $name 插件名称
	 * @return void
	 */
	static public function add($tag, $name) {
		if (!isset(self::$tags[$tag])) {
			self::$tags[$tag] = array();
		}
		if (is_array($name)) {
			self::$tags[$tag] = array_merge(self::$tags[$tag], $name);
		} else {
			self::$tags[$tag][] = $name;
		}
	}

	/**
	 * 批量导入插件
	 * @param array $data 插件信息
	 * @param boolean $recursive 是否递归合并
	 * @return void
	 */
	static public function import($data, $recursive = true) {
		//初始化
		if (empty(self::$tags) && empty($data) && C('DB_HOST') && C('DB_NAME') && C('DB_USER')) {
			$tags = cache('Behavior');
			if (empty($tags)) {
				$tags = D('Common/Behavior')->behavior_cache();
			}
			self::$tags = $tags;
		} else if (!C('DB_HOST') && !C('DB_NAME') && !C('DB_USER')) {
			//当没有安装的时候载入初始tag
			$data = array(
				'app_init' => array(
					'Behavior\BuildLiteBehavior',
					'Common\Behavior\AppInitBehavior',
				),
				'app_begin' => array(
					'Behavior\ReadHtmlCacheBehavior',
				),
				'app_end' => array(
					'Behavior\ShowPageTraceBehavior',
				),
				'view_parse' => array(
					'Behavior\ParseTemplateBehavior',
				),
				'template_filter' => array(
					'Behavior\ContentReplaceBehavior',
				),
				'view_filter' => array(
					'Behavior\WriteHtmlCacheBehavior',
				),
			);
		}
		if (!$recursive) {
			// 覆盖导入
			self::$tags = array_merge(self::$tags, $data);
		} else {
			// 合并导入
			foreach ($data as $tag => $val) {
				//兼容tp原来的写法
				if (is_array($val)) {
					foreach ($val as $k => $rs) {
						if (is_array($rs)) {
							$val[$k] = $rs;
						} else {
							$val[$k] = array(
								'_type' => 2,
								'class' => $rs,
							);
						}
					}
				}
				if (!isset(self::$tags[$tag])) {
					self::$tags[$tag] = array();
				}

				if (!empty($val['_overlay'])) {
					// 可以针对某个标签指定覆盖模式
					unset($val['_overlay']);
					self::$tags[$tag] = $val;
				} else {
					// 合并模式
					self::$tags[$tag] = array_merge(self::$tags[$tag], $val);
				}
			}
		}
	}

	/**
	 * 获取插件信息
	 * @param string $tag 插件位置 留空获取全部
	 * @return array
	 */
	static public function get($tag = '') {
		if (empty($tag)) {
			// 获取全部的插件信息
			return self::$tags;
		} else {
			return self::$tags[$tag];
		}
	}

	/**
	 * 监听标签的插件
	 * @param string $tag 标签名称
	 * @param mixed $params 传入参数
	 * @return void
	 */
	static public function listen($tag, &$params = NULL) {
		if (isset(self::$tags[$tag]) && is_array(self::$tags[$tag])) {
			if (APP_DEBUG) {
				G($tag . 'Start');
				trace('[ ' . $tag . ' ] --START--', '', 'INFO');
			}
			foreach (self::$tags[$tag] as $ar) {
				switch ((int) $ar['_type']) {
					//规则行为
					case 1:
						$result = D('Common/Behavior')->execution($ar, $params);
						break;
					case 2:
						$name = $ar['class'];
						APP_DEBUG && G($name . '_start');
						$result = self::exec($name, $tag, $params);
						if (APP_DEBUG) {
							G($name . '_end');
							trace('Run ' . $name . ' [ RunTime:' . G($name . '_start', $name . '_end', 6) . 's ]', '', 'INFO');
						}
						break;
					//SQL规则行为
					case 3:
						$result = D('Common/Behavior')->executionSQL($ar, $params);
						break;
					//插件行为
					case 4:
						$result = D('Addons/Addons')->execution($ar, $params);
						break;
					default:
						continue;
						break;
				}
				if (isset($result) && false === $result) {
					// 如果返回false 则中断插件执行
					return;
				}
			}
			if (APP_DEBUG) {
				// 记录行为的执行日志
				trace('[ ' . $tag . ' ] --END-- [ RunTime:' . G($tag . 'Start', $tag . 'End', 6) . 's ]', '', 'INFO');
			}
		}
		return;
	}

	/**
	 * 执行某个插件
	 * @param array $name 规则
	 * @param string $tag 方法名（标签名）
	 * @param Mixed $params 传入的参数
	 * @return boolean|array
	 */
	static public function exec($name, $tag, &$params = NULL) {
		if ('Behavior' == substr($name, -8)) {
			// 行为扩展必须用run入口方法
			$tag = 'run';
		}
		if (empty($name)) {
			return false;
		}
		$addon = new $name();
		return $addon->$tag($params);
	}

}
