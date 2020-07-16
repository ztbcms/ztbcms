<?php
/**
 * author: Devtool
 * created: 2020-07-14 11:33:14
 */

namespace Admin\Service;

use Admin\Model\AdminMessageModel;
use System\Service\BaseService;

/**
 * 后台消息中心服务
 */
class AdminMessageService extends BaseService {

    /**
     * 根据ID获取后台消息
     *
     * @param $id
     * @return array
     */
    static function getAdminMessageById($id) {
        return self::find('AdminMessage', ['id' => $id]);
    }


    /**
     * 获取后台消息列表
     *
     * @param array  $where
     * @param string $order
     * @param int    $page
     * @param int    $limit
     * @param bool   $isRelation
     * @return array
     */
    static function getAdminMessageList($where = [], $order = '', $page = 1, $limit = 20, $isRelation = false) {
        return self::select('AdminMessage', $where, $order, $page, $limit, $isRelation);
    }


    /**
     *
     * 创建消息
     *
     * @param string $title               消息标题
     * @param string $content             消息内容
     * @param string $receiver            接收者
     * @param string $target              消息来源
     * @param string $target_type         消息来源类型
     * @param string $receiver_type       接收者类型
     * @return array
     */
    static function createMessage($title, $content, $receiver, $target = "system", $target_type = "system", $receiver_type = "admin_user_id") {
        $data = [
            'title'         => $title,
            'content'       => $content,
            'target'        => $target,
            'target_type'   => $target_type,
            'receiver'      => $receiver,
            'receiver_type' => $receiver_type,
            'create_time'   => time()
        ];
        return self::create('AdminMessage', $data);
    }

    /**
     *
     * 创建系统消息 （群发到每一个管理员）
     *
     * @param string $title               消息标题
     * @param string $content             消息内容
     * @param string $target              消息来源
     * @param string $target_type         消息来源类型
     * @param string $receiver_type       接收者类型
     * @return array
     */
    static function createSystemMessage($title, $content, $target = "system", $target_type = "system", $receiver_type = "admin_user_id") {
        $admin_ids = M('user')->where(['status'=>1])->getField('id',true);
        if($admin_ids){
            foreach ($admin_ids as $uid){
                $data = [
                    'title'         => $title,
                    'content'       => $content,
                    'target'        => $target,
                    'target_type'   => $target_type,
                    'receiver'      => $uid,
                    'receiver_type' => $receiver_type,
                    'type'          => AdminMessageModel::SYSTEM_TYPE,
                    'create_time'   => time(),
                ];
                self::create('AdminMessage', $data);
            }
            return self::createReturn(true, 1, '操作成功');
        }
        return self::createReturn(false, 0, '操作失败');
    }

    /**
     * 群发消息
     *
     * @param string $title               消息标题
     * @param string $content             消息内容
     * @param string $target              消息来源
     * @param string $target_type         消息来源类型
     * @param string $receiver_type       接收者类型
     * @return array
     */
    static function createGroupMessage($title, $content, $target="system", $target_type="system", $receiver_type = "admin_user_id") {
        // 对所有管理员，发送消息
        $admin_ids = M('user')->where(['status'=>1])->getField('id',true);
        if($admin_ids){
            foreach ($admin_ids as $uid){
                $data = [
                    'title' => $title,
                    'content' => $content,
                    'target' => $target,
                    'target_type' => $target_type,
                    'receiver' => $uid,
                    'receiver_type' => $receiver_type,
                    'create_time' => time(),
                ];
                self::create('AdminMessage', $data);
            }
            return self::createReturn(true, 1, '操作成功');
        }
        return self::createReturn(false, 0, '操作失败');
    }


    /**
     * 更新后台消息中心
     *
     * @param       $id
     * @param array $data
     * @return array
     */
    static function updateAdminMessage($id, $data = []) {
        return self::update('AdminMessage', ['id' => $id], $data);
    }

    /**
     *
     * 阅读消息（支持批量）
     *
     * @param $ids
     * @param $receiver
     * @param $read_status
     * @return array
     */
    static function readAdminMessage($ids, $receiver, $read_status){
        return self::update('AdminMessage',['id'=>[ 'in',$ids ],'receiver'=>$receiver],['read_status'=>$read_status,'read_time'=>time()]);
    }

    /**
     *
     * 已读所有
     *
     * @param $where
     * @param $read_status
     * @return array
     */
    static function readAllAdminMessage($where , $read_status){
        return self::update('AdminMessage',$where,['read_status'=>$read_status,'read_time'=>time()]);
    }


    /**
     * 删除消息
     *
     * @param $id
     * @return array
     */
    static function deleteAdminMessageById($id) {
        return self::delete('AdminMessage', ['id' => $id]);
    }
}