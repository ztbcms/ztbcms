<?php

// +----------------------------------------------------------------------
// |  Wap切换行为 app_begin
// +----------------------------------------------------------------------

namespace Wap\Behavior;

class WapBehavior {

    public function run(&$data) {
        if ($this->isMobile() && MODULE_NAME == 'Content' && $this->checkActionExist()) {
            $parameter = $_GET;
            unset($parameter[C('VAR_MODULE')], $parameter[C('VAR_CONTROLLER')], $parameter[C('VAR_ACTION')]);
            redirect(U("Wap/" . CONTROLLER_NAME . "/" . ACTION_NAME, $parameter));
        }
    }

    //判断是否属手机
    protected function isMobile() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320", "acer", "acoon", "acs-", "abacho", "ahong", "airness", "alcatel", "amoi", "android", "applewebkit/525", "applewebkit/532", "asus", "audio", "au-mic", "avantogo", "becker", "benq", "bilbo", "bird", "blackberry", "blazer", "bleu", "cdm-", "compal", "coolpad", "danger", "dbtel", "dopod", "elaine", "eric", "etouch", "fly ", "fly_", "fly-", "go.web", "goodaccess", "gradiente", "grundig", "haier", "hedy", "hitachi", "htc", "huawei", "hutchison", "inno", "ipad", "ipaq", "ipod", "jbrowser", "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2", "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-", "lge-", "lge9", "longcos", "maemo", "mercator", "meridian", "micromax", "midp", "mini", "mitsu", "mmm", "mmp", "mobi", "mot-", "moto", "nec-", "netfront", "newgen", "nexian", "nf-browser", "nintendo", "nitro", "nokia", "nook", "novarra", "obigo", "palm", "panasonic", "pantech", "philips", "phone", "pg-", "playstation", "pocket", "pt-", "qc-", "qtek", "rover", "sagem", "sama", "samu", "sanyo", "samsung", "sch-", "scooter", "sec-", "sendo", "sgh-", "sharp", "siemens", "sie-", "softbank", "sony", "spice", "sprint", "spv", "symbian", "tablet", "talkabout", "tcl-", "teleca", "telit", "tianyu", "tim-", "toshiba", "tsm", "up.browser", "utec", "utstar", "verykool", "virgin", "vk-", "voda", "voxtel", "vx", "wap", "wellco", "wig browser", "wii", "windows ce", "wireless", "xda", "xde", "zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }

        return $is_mobile;
    }
    
    /**
     * 检测Wap模块下是否存在对应的Controller/Action
     *
     * @return bool
     */
    private function checkActionExist() {
        $cls = "\\Wap\\Controller\\" . CONTROLLER_NAME . 'Controller';
        if (class_exists($cls)) {
            if (method_exists($cls, ACTION_NAME)) {
                return true;
            }
        }

        return false;
    }

}
