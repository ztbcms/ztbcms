<?php

// +----------------------------------------------------------------------
// |  Wap切换行为 app_begin
// +----------------------------------------------------------------------

namespace Wap\Behavior;

class WapBehavior {

	public function run(&$data) {
		if (MODULE_NAME == 'Content' && CONTROLLER_NAME == 'Index' && $this->isMobile()) {
			$catid = I('catid', 0, 'intval');
			$id = I('id', 0, 'intval');
			$parameter = $_GET;
			if ($catid && $id) {
				$action = 'shows';
			} else if ($catid && $id == 0) {
				$action = 'lists';
			} else {
				$action = 'index';
			}
			unset($parameter[C('VAR_MODULE')], $parameter[C('VAR_CONTROLLER')], $parameter[C('VAR_ACTION')]);
			redirect(U("Wap/Index/{$action}", $parameter));
		}
	}

	//判断是否属手机
	protected function isMobile() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$mobile_agents = Array("240x320", "acer", "acoon", "acs-", "abacho", "ahong", "airness", "alcatel", "amoi", "android", "anywhereyougo.com", "applewebkit/525", "applewebkit/532", "asus", "audio", "au-mic", "avantogo", "becker", "benq", "bilbo", "bird", "blackberry", "blazer", "bleu", "cdm-", "compal", "coolpad", "danger", "dbtel", "dopod", "elaine", "eric", "etouch", "fly ", "fly_", "fly-", "go.web", "goodaccess", "gradiente", "grundig", "haier", "hedy", "hitachi", "htc", "huawei", "hutchison", "inno", "ipad", "ipaq", "ipod", "jbrowser", "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2", "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-", "lge-", "lge9", "longcos", "maemo", "mercator", "meridian", "micromax", "midp", "mini", "mitsu", "mmm", "mmp", "mobi", "mot-", "moto", "nec-", "netfront", "newgen", "nexian", "nf-browser", "nintendo", "nitro", "nokia", "nook", "novarra", "obigo", "palm", "panasonic", "pantech", "philips", "phone", "pg-", "playstation", "pocket", "pt-", "qc-", "qtek", "rover", "sagem", "sama", "samu", "sanyo", "samsung", "sch-", "scooter", "sec-", "sendo", "sgh-", "sharp", "siemens", "sie-", "softbank", "sony", "spice", "sprint", "spv", "symbian", "tablet", "talkabout", "tcl-", "teleca", "telit", "tianyu", "tim-", "toshiba", "tsm", "up.browser", "utec", "utstar", "verykool", "virgin", "vk-", "voda", "voxtel", "vx", "wap", "wellco", "wig browser", "wii", "windows ce", "wireless", "xda", "xde", "zte");
		$is_mobile = false;
		foreach ($mobile_agents as $device) {
			if (stristr($user_agent, $device)) {
				$is_mobile = true;
				break;
			}
		}
		return $is_mobile;
	}

}
