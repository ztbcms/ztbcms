<?php
/**
 * author: Devtool
 * created: 2020-07-14 11:33:14
 */

namespace AdminMessage\Service;

use System\Service\BaseService;

/**
 * 后台消息中心服务
 */
class AdminMessageService extends BaseService {

    /**
     * 根据ID获取后台消息中心
     *
     * @param $id
     * @return array
     */
    static function getAdminMessageById($id) {
        return self::find('AdminMessage', ['id' => $id]);
    }


    /**
     * 获取后台消息中心列表
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
     * 添加后台消息中心
     *
     * @param $title
     * @param $content
     * @param $target
     * @param $target_type
     * @param $sender
     * @param $sender_type
     * @param $receiver
     * @param $receiver_type
     * @return array
     */
    static function createAdminMessage($title,$content,$target,$target_type,$sender,$sender_type,$receiver,$receiver_type) {
        // 所有管理员，添加消息记录
        if($receiver == 0){
            $ids = M('user')->where(['status'=>1])->getField('id',true);
            foreach ($ids as $uid){
                $data = [
                    'title' => $title,
                    'content' => $content,
                    'target' => $target,
                    'target_type' => $target_type,
                    'sender' => $sender,
                    'sender_type' => $sender_type,
                    'receiver' => $uid,
                    'receiver_type' => $receiver_type,
                    'create_time' => time(),
                ];
                self::create('AdminMessage', $data);
            }
            return self::createReturn(true, 1, '操作成功');;
        }
        $data = [
            'title' => $title,
            'content' => $content,
            'target' => $target,
            'target_type' => $target_type,
            'sender' => $sender,
            'sender_type' => $sender_type,
            'receiver' => $receiver,
            'receiver_type' => $receiver_type,
            'create_time' => time(),
        ];
        return self::create('AdminMessage', $data);
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

    // 已读
    static function readAdminMessage($id, $receiver, $read_status){
        return self::update('AdminMessage',['id'=>$id,'receiver'=>$receiver],['read_status'=>$read_status,'read_time'=>time()]);
    }

    // 已读所有
    static function readAdminAllMessage($where , $read_status){
        return self::update('AdminMessage',$where,['read_status'=>$read_status,'read_time'=>time()]);
    }

    // 已读IDS
    static function readAdminMessageByIds($ids, $receiver, $read_status){
        return self::update('AdminMessage',['id'=> ['in',$ids],'receiver'=>$receiver],['read_status'=>$read_status,'read_time'=>time()]);
    }

    /**
     * 删除后台消息中心
     *
     * @param $id
     * @return array
     */
    static function deleteAdminMessageById($id) {
        return self::delete('AdminMessage', ['id' => $id]);
    }
}