<?php

// +----------------------------------------------------------------------
// |  常用菜单
// +----------------------------------------------------------------------

namespace Admin\Model;

use Common\Model\Model;

class AdminPanelModel extends Model {

    /**
     * 添加常用菜单
     * @param type $data
     * @return boolean
     */
    public function addPanel($data) {
        //删除旧的
        $this->where(array("userid" => \Admin\Service\User::getInstance()->id))->delete();
        if (empty($data)) {
            return true;
        }
        C('TOKEN_ON', false);
        foreach ($data as $k => $rs) {
            $data[$k] = $this->create($rs, 1);
        }

        return $this->addAll($data) !== false ? true : false;
    }

    /**
     * 返回某个用户的全部常用菜单
     * @param type $userid 用户ID
     * @return type
     */
    public function getAllPanel($userid) {
        return $this->where(array('userid' => $userid))->select();
    }

    /**
     * 检查该菜单是否已经添加过
     * @param type $mid 菜单ID
     * @return boolean
     */
    public function isExist($mid) {
        return $this->where(array('mid' => $mid, "userid" => \Admin\Service\User::getInstance()->id))->count();
    }

}
