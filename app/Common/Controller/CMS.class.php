<?php

// +----------------------------------------------------------------------
// | Controller
// +----------------------------------------------------------------------

namespace Common\Controller;

use Libs\System\Components;

class CMS extends \Think\Controller {

	//缓存
	public static $Cache = array();
	//当前对象
	private static $_app;

	public function __get($name) {
		$parent = parent::__get($name);
		if (empty($parent)) {
			return Components::getInstance()->$name;
		}
		return $parent;
	}

	public function __construct() {
		parent::__construct();
		self::$_app = $this;
	}

	//初始化
	protected function _initialize() {
		$this->initSite();
		//默认跳转时间
		$this->assign("waitSecond", 3000);
	}

	/**
	 * 获取CMS 对象
	 * @return type
	 */
	public static function app() {
		return self::$_app;
	}

	/**
	 * 初始化站点配置信息
	 * @return Arry 配置数组
	 */
	protected function initSite() {
		$Config = cache("Config");
		self::$Cache['Config'] = $Config;
		$config_siteurl = $Config['siteurl'];
		if (isModuleInstall('Domains')) {
			$parse_url = parse_url($config_siteurl);
			$config_siteurl = (is_ssl() ? 'https://' : 'http://') . "{$_SERVER['HTTP_HOST']}{$parse_url['path']}";
		}
		defined('CONFIG_SITEURL_MODEL') or define('CONFIG_SITEURL_MODEL', $config_siteurl);
		$this->assign("config_siteurl", $config_siteurl);
		$this->assign("Config", $Config);
	}

	/**
	 * Ajax方式返回数据到客户端
	 * @access protected
	 * @param mixed $data 要返回的数据
	 * @param String $type AJAX返回数据格式
	 * @param int $json_option 传递给json_encode的option参数
	 * @return void
	 */
	protected function ajaxReturn($data, $type = '', $json_option = 0) {
		$data['state'] = $data['status'] ? "success" : "fail";
		if (empty($type)) {
			$type = C('DEFAULT_AJAX_RETURN');
		}

		switch (strtoupper($type)) {
			case 'JSON':
				// 返回JSON数据格式到客户端 包含状态信息
				header('Content-Type:text/html; charset=utf-8');
				exit(json_encode($data, $json_option));
			case 'XML':
				// 返回xml格式数据
				header('Content-Type:text/xml; charset=utf-8');
				exit(xml_encode($data));
			case 'JSONP':
				// 返回JSON数据格式到客户端 包含状态信息
				header('Content-Type:application/json; charset=utf-8');
				$handler = isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
				exit($handler . '(' . json_encode($data, $json_option) . ');');
			case 'EVAL':
				// 返回可执行的js脚本
				header('Content-Type:text/html; charset=utf-8');
				exit($data);
			default:
				// 用于扩展其他返回格式数据
				tag('ajax_return', $data);
		}
	}

	/**
	 * 分页输出
	 * @param type $total 信息总数
	 * @param type $size 每页数量
	 * @param type $number 当前分页号（页码）
	 * @param type $config 配置，会覆盖默认设置
	 * @return type
	 */
	protected function page($total, $size = 0, $number = 0, $config = array()) {
		return page($total, $size, $number, $config);
	}

	/**
	 * 返回模型对象
	 * @param type $model
	 * @return type
	 */
	protected function getModelObject($model) {
		if (is_string($model) && strpos($model, '/') == false) {
			$model = M(ucwords($model));
		} else if (strpos($model, '/') && is_string($model)) {
			$model = D($model);
		} else if (is_object($model)) {
			return $model;
		} else {
			$model = M();
		}
		return $model;
	}

	/**
	 * 基本信息分页列表方法
	 * @param type $model 可以是模型对象，或者表名，自定义模型请传递完整（例如：Content/Model）
	 * @param type $where 条件表达式
	 * @param type $order 排序
	 * @param type $limit 每次显示多少
	 */
	protected function basePage($model, $where = '', $order = '', $limit = 20) {
		$model = $this->getModelObject($model);
		$count = $model->where($where)->count();
		$page = $this->page($count, $limit);
		$data = $model->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->assign('Page', $page->show());
		$this->assign('data', $data);
		$this->assign('count', $count);
		$this->display();
	}

	/**
	 * 基本信息添加
	 * @param type $model 可以是模型对象，或者表名，自定义模型请传递完整（例如：Content/Model）
	 * @param type $u 添加成功后的跳转地址
	 * @param type $data 需要添加的数据
	 */
	protected function baseAdd($model, $u = 'index', $data = '') {
		$model = $this->getModelObject($model);
		if (IS_POST) {
			if (empty($data)) {
				$data = I('post.', '', '');
			}
			if ($model->create($data) && $model->add()) {
				$this->success('添加成功！', $u ? U($u) : '');
			} else {
				$error = $model->getError();
				$this->error($error ?: '添加失败！');
			}
		} else {
			$this->display();
		}
	}

	/**
	 * 基础修改信息方法
	 * @param type $model 可以是模型对象，或者表名，自定义模型请传递完整（例如：Content/Model）
	 * @param type $u 修改成功后的跳转地址
	 * @param type $data 需要修改的数据
	 */
	protected function baseEdit($model, $u = 'index', $data = '') {
		$model = $this->getModelObject($model);
		$fidePk = $model->getPk();
		$pk = I('request.' . $fidePk, '', '');
		if (empty($pk)) {
			$this->error('请指定需要修改的信息！');
		}
		$where = array($fidePk => $pk);
		if (IS_POST) {
			if (empty($data)) {
				$data = I('post.', '', '');
			}
			if ($model->create($data) && $model->where($where)->save() !== false) {
				$this->success('修改成功！', $u ? U($u) : '');
			} else {
				$error = $model->getError();
				$this->error($error ?: '修改失败！');
			}
		} else {
			$data = $model->where($where)->find();
			if (empty($data)) {
				$this->error('该信息不存在！');
			}
			$this->assign('data', $data);
			$this->display();
		}
	}

	/**
	 * 基础信息单条记录删除，根据主键
	 * @param type $model 可以是模型对象，或者表名，自定义模型请传递完整（例如：Content/Model）
	 * @param type $u 删除成功后跳转地址
	 */
	protected function baseDelete($model, $u = 'index') {
		$model = $this->getModelObject($model);
		$pk = I('request.' . $model->getPk());
		if (empty($pk)) {
			$this->error('请指定需要修改的信息！');
		}
		$where = array($model->getPk() => $pk);
		$data = $model->where($where)->find();
		if (empty($data)) {
			$this->error('该信息不存在！');
		}
		if ($model->delete() !== false) {
			$this->success('删除成功！', $u ? U($u) : '');
		} else {
			$error = $model->getError();
			$this->error($error ?: '删除失败！');
		}
	}

	/**
	 * 验证码验证
	 * @param type $verify 验证码
	 * @param type $type 验证码类型
	 * @return boolean
	 */
	static public function verify($verify, $type = "verify") {
		return A('Api/Checkcode')->validate($type, $verify);
	}

	//空操作
	public function _empty() {
		$this->error('该页面不存在！');
	}

	static public function logo() {
		return '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0p8zAABAXGlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS41LWMwMjEgNzkuMTU1NzcyLCAyMDE0LzAxLzEzLTE5OjQ0OjAwICAgICAgICAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIKICAgICAgICAgICAgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iCiAgICAgICAgICAgIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiCiAgICAgICAgICAgIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIgogICAgICAgICAgICB4bWxuczpwaG90b3Nob3A9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGhvdG9zaG9wLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIgogICAgICAgICAgICB4bWxuczpleGlmPSJodHRwOi8vbnMuYWRvYmUuY29tL2V4aWYvMS4wLyI+CiAgICAgICAgIDx4bXA6Q3JlYXRvclRvb2w+QWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKFdpbmRvd3MpPC94bXA6Q3JlYXRvclRvb2w+CiAgICAgICAgIDx4bXA6Q3JlYXRlRGF0ZT4yMDE2LTA1LTA2VDExOjU0OjQxKzA4OjAwPC94bXA6Q3JlYXRlRGF0ZT4KICAgICAgICAgPHhtcDpNZXRhZGF0YURhdGU+MjAxNi0wNS0wNlQxMjowMzo0MyswODowMDwveG1wOk1ldGFkYXRhRGF0ZT4KICAgICAgICAgPHhtcDpNb2RpZnlEYXRlPjIwMTYtMDUtMDZUMTI6MDM6NDMrMDg6MDA8L3htcDpNb2RpZnlEYXRlPgogICAgICAgICA8ZGM6Zm9ybWF0PmltYWdlL3BuZzwvZGM6Zm9ybWF0PgogICAgICAgICA8eG1wTU06SW5zdGFuY2VJRD54bXAuaWlkOjcwM2I1MjY3LTk4YzEtM2M0Ni1hZTc0LTU0MTk3NWU2MGYxZDwveG1wTU06SW5zdGFuY2VJRD4KICAgICAgICAgPHhtcE1NOkRvY3VtZW50SUQ+YWRvYmU6ZG9jaWQ6cGhvdG9zaG9wOmRhMmIwYjdkLTEzM2UtMTFlNi1iY2NjLWM2NzViZWI5ZTdlMDwveG1wTU06RG9jdW1lbnRJRD4KICAgICAgICAgPHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD54bXAuZGlkOjVhMDRkZTMzLTFiM2MtNTc0My1iYjdkLTEzYjJlOGY5NWY5ZDwveG1wTU06T3JpZ2luYWxEb2N1bWVudElEPgogICAgICAgICA8eG1wTU06SGlzdG9yeT4KICAgICAgICAgICAgPHJkZjpTZXE+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPmNyZWF0ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDo1YTA0ZGUzMy0xYjNjLTU3NDMtYmI3ZC0xM2IyZThmOTVmOWQ8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDp3aGVuPjIwMTYtMDUtMDZUMTE6NTQ6NDErMDg6MDA8L3N0RXZ0OndoZW4+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpzb2Z0d2FyZUFnZW50PkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE0IChXaW5kb3dzKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPnNhdmVkPC9zdEV2dDphY3Rpb24+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDppbnN0YW5jZUlEPnhtcC5paWQ6ODQxOTQzZmYtNjk4NC02ODRjLTg1MTAtYzI2MDU2Y2ViNmY4PC9zdEV2dDppbnN0YW5jZUlEPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6d2hlbj4yMDE2LTA1LTA2VDEyOjAzOjQzKzA4OjAwPC9zdEV2dDp3aGVuPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxNCAoV2luZG93cyk8L3N0RXZ0OnNvZnR3YXJlQWdlbnQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDpjaGFuZ2VkPi88L3N0RXZ0OmNoYW5nZWQ+CiAgICAgICAgICAgICAgIDwvcmRmOmxpPgogICAgICAgICAgICAgICA8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmFjdGlvbj5jb252ZXJ0ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OnBhcmFtZXRlcnM+ZnJvbSBhcHBsaWNhdGlvbi92bmQuYWRvYmUucGhvdG9zaG9wIHRvIGltYWdlL3BuZzwvc3RFdnQ6cGFyYW1ldGVycz4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPmRlcml2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OnBhcmFtZXRlcnM+Y29udmVydGVkIGZyb20gYXBwbGljYXRpb24vdm5kLmFkb2JlLnBob3Rvc2hvcCB0byBpbWFnZS9wbmc8L3N0RXZ0OnBhcmFtZXRlcnM+CiAgICAgICAgICAgICAgIDwvcmRmOmxpPgogICAgICAgICAgICAgICA8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmFjdGlvbj5zYXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6aW5zdGFuY2VJRD54bXAuaWlkOjcwM2I1MjY3LTk4YzEtM2M0Ni1hZTc0LTU0MTk3NWU2MGYxZDwvc3RFdnQ6aW5zdGFuY2VJRD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxNi0wNS0wNlQxMjowMzo0MyswODowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OnNvZnR3YXJlQWdlbnQ+QWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKFdpbmRvd3MpPC9zdEV2dDpzb2Z0d2FyZUFnZW50PgogICAgICAgICAgICAgICAgICA8c3RFdnQ6Y2hhbmdlZD4vPC9zdEV2dDpjaGFuZ2VkPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgPC9yZGY6U2VxPgogICAgICAgICA8L3htcE1NOkhpc3Rvcnk+CiAgICAgICAgIDx4bXBNTTpEZXJpdmVkRnJvbSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgIDxzdFJlZjppbnN0YW5jZUlEPnhtcC5paWQ6ODQxOTQzZmYtNjk4NC02ODRjLTg1MTAtYzI2MDU2Y2ViNmY4PC9zdFJlZjppbnN0YW5jZUlEPgogICAgICAgICAgICA8c3RSZWY6ZG9jdW1lbnRJRD54bXAuZGlkOjVhMDRkZTMzLTFiM2MtNTc0My1iYjdkLTEzYjJlOGY5NWY5ZDwvc3RSZWY6ZG9jdW1lbnRJRD4KICAgICAgICAgICAgPHN0UmVmOm9yaWdpbmFsRG9jdW1lbnRJRD54bXAuZGlkOjVhMDRkZTMzLTFiM2MtNTc0My1iYjdkLTEzYjJlOGY5NWY5ZDwvc3RSZWY6b3JpZ2luYWxEb2N1bWVudElEPgogICAgICAgICA8L3htcE1NOkRlcml2ZWRGcm9tPgogICAgICAgICA8cGhvdG9zaG9wOkRvY3VtZW50QW5jZXN0b3JzPgogICAgICAgICAgICA8cmRmOkJhZz4KICAgICAgICAgICAgICAgPHJkZjpsaT54bXAuZGlkOmU4NzhiMGFiLWE0OGUtZjg0Zi05YzRkLTk1NzVkMzIwOWQ4YzwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpCYWc+CiAgICAgICAgIDwvcGhvdG9zaG9wOkRvY3VtZW50QW5jZXN0b3JzPgogICAgICAgICA8cGhvdG9zaG9wOkNvbG9yTW9kZT4zPC9waG90b3Nob3A6Q29sb3JNb2RlPgogICAgICAgICA8cGhvdG9zaG9wOklDQ1Byb2ZpbGU+c1JHQiBJRUM2MTk2Ni0yLjE8L3Bob3Rvc2hvcDpJQ0NQcm9maWxlPgogICAgICAgICA8dGlmZjpPcmllbnRhdGlvbj4xPC90aWZmOk9yaWVudGF0aW9uPgogICAgICAgICA8dGlmZjpYUmVzb2x1dGlvbj43MjAwMDAvMTAwMDA8L3RpZmY6WFJlc29sdXRpb24+CiAgICAgICAgIDx0aWZmOllSZXNvbHV0aW9uPjcyMDAwMC8xMDAwMDwvdGlmZjpZUmVzb2x1dGlvbj4KICAgICAgICAgPHRpZmY6UmVzb2x1dGlvblVuaXQ+MjwvdGlmZjpSZXNvbHV0aW9uVW5pdD4KICAgICAgICAgPGV4aWY6Q29sb3JTcGFjZT4xPC9leGlmOkNvbG9yU3BhY2U+CiAgICAgICAgIDxleGlmOlBpeGVsWERpbWVuc2lvbj4xODA8L2V4aWY6UGl4ZWxYRGltZW5zaW9uPgogICAgICAgICA8ZXhpZjpQaXhlbFlEaW1lbnNpb24+MTgwPC9leGlmOlBpeGVsWURpbWVuc2lvbj4KICAgICAgPC9yZGY6RGVzY3JpcHRpb24+CiAgIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9InciPz556WLnAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAABclSURBVHja7J15eFTl9cc/72Qmy2SSAEEEFUEUa5VNrCiLighoxQpFNlHbn9rFVJ9qq9VatVaruNVf69LiglqtigguIFIUVFRAhSoIbhVEEFBEgiSZmSyTmdM/5qZFTMIs99659877fZ48DyQzd/3cc8973nPOq0QELS2vyKcvgZYGWktLA62lpYHW0tJAa2mgtbQ00FpaGmgtLQ20lpYGWksDraWlgdbS0kBraWmgtbQ00Fr5JKUvQfoSET9wMNAb6Gn8HAhUAp2Nn1IgCAT2+Ho90ATUAF8B1cAOYBOwAdgIrFdKbdRXWgNtBbwFQD9gCHC08e/DgSKLd10LrAXWAG8By5VS6/Qd0UBnAnEf4BRgNDAYCDnk0LYDrwMLgYVKqS36bmmgWwPYBwwFJgFjge65Opb7P2xkyL5+juhUkMrH1wJzgSeVUmv1ndQg9xWRO0Tkc8mxdjUmZPKLdcL0anlla1Mmm3hfRK4Skf20hc4viEuAqcBPgWOccEwrtzczZXGYDbUJAOZ/v4wxPQKZbi4BLADuA55XSiXy6f768gjkLiLyR2AzMMMJMAvwp3cbGPps7X9hBmiIS7b39DRgHvCRiPzCeIg10B4BuZuI3EMyLHY1ydBazvVVvTBmQR2/eSNKbA8bWhczrbVEb+CvwGYR+a2IhDTQ7gW5q4j8hWRs9+dAsVOO7ZWtMfrPruGfn8Va/XskZnqvlErgJmCjiFzqZYvt8yDIRSJyBbAOuNhJIMcFrllZz8j5dXwRbdu1bYhbdgiVwJ+Af4vIFBFRGmhnwzwW+BC4GefEjgHYEkkwfG4tN7xdT2IvBrguZnk3q+7ATOB1ETlSA+1MP/kp4FngIKcd37yNTfR7soal25pT+nwkZlt7tqHAShG5VUSCGmhnwHyeYZXHW7H9tTvjXPZGNKPvNsbh4mVRxi4M83Vj6pBa6HK0pgLgN8AaERmugc4dyJUi8gzwAFBhxT7u+6CRQU/V8synTWl/d11NnMHP1HDn2oa0v1sXy0kDzYOBl0XkZhEJaKDthflEkkk746zYfm2TMGVRmJ+/FqEhLmyqS9CUxvTEox83MXBOLat2ZGZqI7GcdYRVwBXAGyLSWwNtPcjKiGAsBiyZ4n37q2aOnFPDrE+avhGd2FC7dzjDMeH/XolwzsthwllAmeXEihk6yvCtT9dAWwdzGTDHiGCYftwC/GVNA0Oe+easXYtW78Xart4R53tP1fLwvxuzPpa6mCN6dlcAc0Xkj0bylgbaRJgPIJk2acnAb2ejMG5hmF8tj7bpWjzwUdug/vW9Bo59poZ/7zJnNBeJOeryXw3MdksUxOcCmAeQTHDvb8X2l21rZsDsGuZtbH/gt3hLjF8tj37DHdgWTTD+hTAXLY3SaGJkojHuuFUVxhsDxi5O50U5HOYRJGPLZWZvOyFw6+oGrl4RJR1+9i3xccJ+fsIx4dXPm4k0mw9fjzIfG8/q4MRb8gkw0snlYcrBMJ9q+Mym5x1sr09w9ksRFm2JOfLc9ylRbP9xR6femq3AcKXUeu1ypA7z6YZlNh3ml7bG6D+71rEwAzQ042TtDywXkb4a6NRgHmNYZlOD+y2JQaPn17Et6uycd4dEOdp9iQCLROQQpx2Y34E+82yzYd4SSXDW4jCvfdGMWxRtFoJ+Rw9x9gWWiMgQpdRn2kJ/G+YjSRZ8mupmLPgsxoDZNa6CGWzP58jG/VgsIp010N+EuTvwPCamfMYScNkbUcYsqKO6wX2Li9Y2ueaYewPPikixBjoJc7kBczeztvlpXYJhz9Zy+7sNuFXRZlc9hEOBh51QMODLMcwK+Adg2oh5zoYmBs6pYcX2Ztyshrjr3iqTgKvy3UJfBZxuFgAXvh5h4othdjW6f/1yF7kcu+s6ETk5L6McIjIauM6MbX1cE2fSi2HerY7jFUXd+YLxATNF5Eil1Ka8sdAiso/hamS9/0c/bmLg7FpPwexSl6NFHYHHjSaX3gfa8JsfBLJKdIk0C+cauceRZsFrcqnL0aIhwO/yxUJfQLKzT8ZauzPO0U/V8ncTco+1y2GZrhWRQZ4G2og335LNNlrq/D78Oo6X5WKXo0UFwIN21yfabaH/RoapoHvW+XldLnc5WnQEcKUngRaRyZm6Gq3V+XldUe+MC64SkUM9BbRRvnNb2t+j/To/T7sczZ45lULgDq9Z6MtJsyt+KnV+nnY5Yp5yq04Rke97AmijwPXydL6Tap2fdjlcpf83Vg9zvYW+mhRTQhMCN69q4IS5tWwOJ8hnNXgP6MOAs10NtIj0As5L5bPb6xOc8nwdV76VXtGqdjlcpetFpNDNFvr3pFB94oY6P/tdDk+eVvdUDZzjgDYmUdp9xbipzk+7HKbpCit9aSst9MUkZ4ta1ZZIghHzUmsArl0OT6knFnXAsgxoowrlZ2393a11fva6HJ5+yi9zm4U+l1amuN1e52evy+Hp0ztaRI41bWvTq0uZXj3NSqB/sucvvFDnZ6fqYp5/4H9qEsx9gZXALywBWkSGAH12/52b6/z2L/Xx8IhS7XaYrymGa5oNzD8BVgDfJTnFbkkJ1rn/fW3GhUuXR/nb++7MWz6tR4CHTgyRkNyA1RCHoN+zQAeBycD9GYBcRnLp5ym7/bbEdAtt5L6eAck6v2OfrnUlzAEf3DY4yLzvl9G5WBEK5KY63yMppO1a6QxgHgC80+p3p1cXm/38nwJ0fPTjJi54LeLK0qieZT5mjQoxqMv/Lk3Qr/ApbA8v1jd7HujhItJFKbU9RZgvAm5vcS9akblAf90oU3+9POra0qgzehUy44RSOhR92yKXBRQ1NlvMhrjXecZnuB137QXkDsCMlrd/OyoxzeUovX9n/2Ofrp3oRpiLCuDuYUHmjA61CjNARaH9bkdNfuTNjt0LzIOAVSnADFBkjoWeXv2z+rjc9XFNvMBtV/OQigJmjwoxoHP7h54LP7o+P+adholIUCkV3YMpBVxCsgY11brELMfQ06vLjdHmZDdOX5/Vu5B7ji9NCdayHADdkB9ph0XACGD+blx1Ah4m/ZK9Ql8WMB9lvAomu+0KBv2KGcNLefSkUMqWNxcWuqYpb2ZTT96Nq6HAajKrPy31ZwCyIpl4dEs7o03H6vCOBTw5KsQRndLzjrTLYa3bYXB1BXAD7SS17UUBf5owdwIewqQGi3br/MOKuGtYkJIMOuO3NVjULkf22l6f6BfwsSiW4KQsN1XmTwPmocBM0ix2dYJCAcW9x5cytXfmL5Qy7XJYolc/b+bMxWGfCTADFPhTANlHssg1m1dBzjSgc9LF6F1RkOVDYf+xe3liJS5ww9v1XG9uPny5fy8wdwEeBUa58aJd2KeY2wcHKTLhMQzlJMrhTZi3RROc9VKEl7eaXnLn87cD80kGzF3ddsE6FCkeGF7K+IPMG7PqKIc5enFzjHNejrC93pJJowp/KyAXAH8g2Q7V57YLdkwXPzNHhTiozNxDz8VMoZdcjrjAtSvrmfZOPRaelfLvAfMBwGPA8W68aJf2L+amY4IELHgMywu1y5GptkQSTFkUZtk2y+OQHfy7wXwq8AhQ6bYLVlmseGREiFMPtG7kFvLrXI5MtOCzGD96OWxXyZ3yM706ANwEXOrGC3ZcNz+PjwxxQKm13pGeWElPsQRc+VbU7pK7Cj+wFBjktgumgN8NLOG6o0sosIG1skI9sZKqNtYlOHNxmDe/tP2JLPC7EeZ9S3w8NrKUk/a3LzisJ1ZS0zOfNnHekkiultYrc13F2qgDAjwyopSuQXsDMLmJcrjnvjTG4fI3o9y5NqdV/X4/0Egyhc/RKlDwh++V8LuBJfhyUOIX0umjbeqT2gSTXgzzzo6cP4EhP9DgdKD3L/XxxKgQw7rm7oUS8CUrWxptDKW5oUh21idN/OzViFOONeB4l6OllUDn4pyvi04ooGi02WrWN0tG2YF2vD0uWRbl3g8cVXJX6geiQIXTLljAB9OOCXJp/2KccjtDAWV7C7OGOJQ4zOx8tCvO5EVh1jhv9d5CP+C4dR9aayXgBHUoVNi9gHVNk9CxyDkW2uEtKpzXl6e9VgJOsND2RzqcAU6kWbjodce3qCjyA3WOOJICuH1wkAv7FDv2apXlaQrp+zvjTFoU5gPnr95b4gdyfpSpthLIRwudzOfI3XWZ8WEjv1wWdUvmX+6HG+m0EshPlyM35xqOCRe8FuGxda5aWq/ID9TkxntX3DksyPmHFbnmalXkST7H6h1xJi4Ks77GdfmrQT9g+xXLtJVArlVe6P18junvN/Kr5RFbJ5BMVMAP2Jp0m00rgXx0OewaFNY0CecvifDUBlev3vuqH6i1C4ZsWwnkJdA2DMZWbm9myuIwG2pdW1CQAK4DbrRlUGhWK4HcA23/PndZ6HIIcMeaBi5/M0rMvcUxW4Ezqap8HZJLUlg6jjazlUDOfWgPZdztbBTOeyXC3I2udjHmAedSVbmz5Rd+IGzFnqxoJZCXUQ4LzM0bXzYzeVGYzWHXmuUmkmsd3k1V5TeeeEtcDqtaCeSjD22myyHArasauHpllGb3uhgfA5Opqlzd2h9NT06yspWAHhRmrh0NwjkvhVm4OebmW/AIcCFVlW16FS3po1mrsljx8IkhxvQI4FWVubQ3x6ufNzP1pTCfR1xrliNAFVWV/9jbB01xOfp2Kti5YExZJ6tbCeTcQvtz4XJkDmFCYNo79Vz7r3oS7m3CtMpwMdal8mE/UJ+lWzbtXxMqGgt9XI/HlZMe0RkOCi1siGin7gQup6oy5ZzVliLZTPQlcDZVlYsLL5ATyROV+pWtye2ZhO1e2hrjrMURvqx3rYtRTTIc91y6X8zUR1gMDKCqcrHx/7dwQBqqF/3odKIccYGrV9Qz6rk6N8P8msHWc5l82Wc43Cm7ZcA1wMlUVW5r+aWxJNfb+QC03ZGOVF2OrZEEI+bVcqO13T2tVMv09QiqKrdkuhE/kKqTtRWYQlXl0jb+/gIu7MKUPtD27u+rhsReK7//+VmMc+xriGi6Svyqur5ZzqCq8tVst5Wqy/G88RpY2s5n/pkPFtru2cKEJHtftKZYAq54M8qpC+pcC/OYHoHE6AMCR5oBc4uFbi/brhm4Erh9zynGVrQC2Al08rQPnYPJlUuWRTmsQwHH7vu/KOundQmm5qYhoikq9MGtg4P8sm/xKz6lNpu13fbyoTeSjP+tSGVDSqm4iCwAztZAm6uaJmHos7WcemCAwzsWsKkuwdOfNrk2Q+6QigJmjSplYGc/wFwzt93WxMpTwE+pqvw6ze096XWgc1X7mBCYvynG/E2ujitz9qGFTD/uvzWkCYMZU4Hetdv/G4HLqKq8O8PtLQS+BjpqoLV2V6lf8bfjg/zo0G/UkC5RSn1p5n52HxSuB47NAmaUUjHDuntWuagrdLv6Vxbw9oTyPWEGmGX2vnyG2X8MGNhWSl6aekhHObRadFGfYt4aX8F3OnyrwiMKPGH2/vzAC1RVzjNrg0qp5SLyHtBHuxz5q45FiodOLGVszzYLPJ5QSplez+pLJ/EjDc3QPnT+alhXP2smVbQHM8D9VuzbqnzPv+OQnnlmq0wD3TZMCq45qoQlY8v3tirZSqXUm64BWilVA9znTQutwW1N3YI+XvpBGdentirZnyx7qCw8xzvwYAZeh0KfpncPnXpggDWTKhi+X0pP+0bgadcBrZLTmY9qC+1dBXxw+5Ag808tS2fJkFuUUpbN11vqEIrIwcCHgGcw2BZN0O2RXXkP88HlyVUWjtonrSq+zcAhSinLmoFY+v5USn0CPOipQaGOQzO1dyGrJlakCzPA762E2XILbVjpA0j2Uijxyg1V9+zMS5BL/Yq7hgU5N7MWyB8Bfa10Nyy30IaV3gLc6qUbm4+zhf0qC/jXhPJMYQb4tdUw2wK0oVsN/8kbbkeexaJ/cUQRb40v57AOGTcoXKiUsqUAxBagjZrD32g/2l3qWKR4+uQQfz2ulOKCjM+5CbjYrmO2LaiqlJoFzPfCjc6H6e+hXf2snljBD7NvtnmjUupjzwFt6EI8MCXuZaB9Cq4aWMKS08s5MJQ1Hu8DN9l6/LZGB5T6DLjC7Te93KNAdwv6WHRaGTcMKsGfPRlx4DwjR96bQBu6x+2uhxeT/E/pHuDdiRWM2N+0ObDrlFIr7D4P24FWSglwHrDdvVEO74Ac8MFtg4MsGFPGPiWmPajLgWm5OJ+cLLyplPpKRM4h2cvDddk+XvGhe5X7eGJkiKO7mIrB18BUpVROEtNyBpNS6kXgWj0ozI2mHFLIqgkVZsOcAM5USm3K2aA2x9f1RpILv7jM5XAv0EG/YsbwUmaODFkxFrhWKfVCLs8vp2t9K6XEcD2WAn3dAkUu+kSbob6dCpg1KsR3O1qyJNmThoHKqXLuvxqFkmOAL7TLYZ0uOLyIFWeUWwXzMuDHxoA/v4E2oN5sQB3WQJv/NpkzOsT047Oavm5P64BxSqkGJ5yvYyIMSqlVwFiyWyJD+9C7afC+flZPqOCMXpatFbkVGKmU2uGUc3ZUyEwp9TIwkdR7VmsL3dp1BK48soTXxpbTw7q1Ir8Ehhuzv2ig24b6eWCCk6F2cj5016CPF08rY9oxpkxft6WvgFFKqfVOO3+/E2+KUmqeiIwD5uDASherXI4CBVN7F3F8Nz/hmPD4+iZWbk89J/7k7gEeGVFKlxJL7dRWwzKvdyQ7Tn51isgI4FmgzEnH1RiH4vvNLcPqVe7jyT2KTgX45dIod7/X/njL74Npg4JcNqDY6hv6ieEzb3Syu4XDoR5AckmM/Zx0XIX37TSt4fgZvQp5YHhpq65MLAGHztzFxrrWd3ZQmY+Zo0Ic08Xyl+1bwOlKKUfn4Dg+j0IptRo4BnjXawPDQh/cNSzInNGhNv3ygA9+0KP1KMWkg5PV1zbA/DQwwukwuwJoA+otwHFY2HEnXWU7bdyr3MfyH5ZzUZ/ivX524D7fnAwp8SvuO6GUWaNCdgxQbwAmGmV0jpcfl0gpVSciE4DLSaYm5vRhzCbJf/xBhTx4YmnKMPav/N9t6tOpgCdGhjiiU4HVp1gD/Egp5apcG1elbiqlRCl1CzAS+DynkY4MLGPAB38eEmTOyelZ1iM6FeD3wc8PL2LF+HI7YH4bONptMLvKQu8B9isi0o9kH+pxbvChe5YlW2cNysDfLfTBktPLGdrV8tslJFtOXGN36VReWug9oK5WSv0Q+InxerQXaH/qQI87qJB3JlRkBHOLbID5E2Pg91u3wuxqoHcD+wHgu3YPGFNxOVpcjKdPDtHRuSmnceA2oJ9SaonbefBEs2Ol1BdKqTMM9+NTO/bZYS9A9yzzsXRcOZf0K3ZysH+Z4Stf7pYoRl4AvRvYcw1r/VssTkVtz4c2w8WwWJuBqcBxRpajZ+S5dvRKqUYjEtKb5CoCDXYB7QIXoxq4DPiOUmqmExLyNdCpg71NKXUJ0Au412yw9+zk73AXoxq4EuiplLpdKVXv1fvu+QVDDP/6AqAHyVmvalMGhbtZaAe7GOtItl/rrpS6WSkVRstbEpESETlfRN6ULPTMhkYJ3Fstf363XhLiKMVF5DkR+YGI5N0KRyrP4e5LMo49EeiWznff2xkn2ixOssofAI8DDymlPs/Xe6oXDEmC7QOGApNI1jV2d8mhrwXmAk8qpdbqO6mBbgvwPsApwGhgMBByyKFtB14HFpLsir9F3y0NdLpwFwD9DAv+PePfhwNFFu+61rDAa0gm1y9XSq3Td0QDbQXkfuBgkrHunsbPgUAl0Nn4KQWCfHuNxnqSyzTUkCw2rQZ2AJuADSRXWl3v5DInRwMtIvoqaHlGeuFqLQ20lpYGWktLA62lpYHW0kBraWmgtbQ00FpaGmgtLQ20lgZaS0sDraWlgdbS0kBraWmgtfJK/xkAG0bQrYvLqyIAAAAASUVORK5CYII=';
	}

}
