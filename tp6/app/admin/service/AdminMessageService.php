<?php
/**
 * User: jayinton
 * Date: 2020/9/19
 */

namespace app\admin\service;

use app\admin\model\AdminMessageModel;
use app\common\service\BaseService;
use think\facade\Db;

/**
 * 后台消息
 * Class AdminMessageService
 *
 * @package app\admin\service
 */
class AdminMessageService extends BaseService
{

    /**
     * 根据ID获取后台消息
     *
     * @param $id
     *
     * @return array
     */
    static function getAdminMessageById($id)
    {
        return Db::name('admin_message')->where('id', $id)->findOrEmpty();
    }


    /**
     * 获取后台消息列表
     * @param array $where
     * @param string $order
     * @param int $page
     * @param int $limit
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getAdminMessageList($where = [], $order = '', $page = 1, $limit = 20)
    {
        if (!empty($order)) {
            $order = 'id desc';
        }
        $items = AdminMessageModel::where($where)->order($order)->page($page)->limit($limit)->select();
        $total_items = AdminMessageModel::where($where)->count();
        $total_page = ceil($total_items / $limit);

        return self::createReturnList(true, $items, $page, $limit, $total_items, $total_page);
    }


    /**
     *
     * 创建消息
     *
     * @param string $title 消息标题
     * @param string $content 消息内容
     * @param string $receiver 接收者
     * @param string $sender 发送者
     * @param string $sender_type 发送者类型
     * @param string $target 消息来源
     * @param string $target_type 消息来源类型
     * @param string $receiver_type 接收者类型
     * @param string $type 消息类型
     *
     * @return int|string
     */
    static function createMessage($title, $content, $receiver, $sender = "system", $sender_type = "system", $target = "system", $target_type = "system", $receiver_type = "admin_user_id", $type = "")
    {
        $data = [
            'title'         => $title,
            'content'       => $content,
            'sender'        => $sender,
            'sender_type'   => $sender_type,
            'target'        => $target,
            'target_type'   => $target_type,
            'receiver'      => $receiver,
            'receiver_type' => $receiver_type,
            'type'          => $type,
            'create_time'   => time()
        ];
        return Db::name('admin_message')->insert($data);
    }

    /**
     *
     * 创建系统消息 （群发到每一个管理员）
     *
     * @param string $title 消息标题
     * @param string $content 消息内容
     * @param string $sender 发送者
     * @param string $sender_type 发送者类型
     * @param string $target 消息源
     * @param string $target_type 消息源类型
     * @param string $receiver_type 接收者类型
     *
     * @return array
     */
    static function createSystemMessage($title, $content, $sender = "system", $sender_type = "system", $target = "system", $target_type = "system", $receiver_type = "admin_user_id")
    {
        $admin_ids = Db::name('user')->where('status', 1)->column('id');
        if ($admin_ids) {
            foreach ($admin_ids as $uid) {
                $data = [
                    'title'         => $title,
                    'content'       => $content,
                    'sender'        => $sender,
                    'sender_type'   => $sender_type,
                    'target'        => $target,
                    'target_type'   => $target_type,
                    'receiver'      => $uid,
                    'receiver_type' => $receiver_type,
                    'type'          => AdminMessageModel::SYSTEM_TYPE,
                    'create_time'   => time(),
                ];
                Db::name('admin_message')->insert($data);
            }
            return self::createReturn(true, null, '操作成功');
        }
        return self::createReturn(false, null, '操作失败');
    }

    /**
     * 群发消息
     *
     * @param string $title 消息标题
     * @param string $content 消息内容
     * @param string $sender 发送者
     * @param string $sender_type 发送者类型
     * @param string $target 消息源
     * @param string $target_type 消息源类型
     * @param string $receiver_type 接收者类型
     *
     * @return array
     */
    static function createGroupMessage($title, $content, $sender = "system", $sender_type = "system", $target = "system", $target_type = "system", $receiver_type = "admin_user_id")
    {
        // 对所有管理员，发送消息
        $admin_ids = Db::name('user')->where('status', 1)->column('id');
        if ($admin_ids) {
            foreach ($admin_ids as $uid) {
                $data = [
                    'title'         => $title,
                    'content'       => $content,
                    'sender'        => $sender,
                    'sender_type'   => $sender_type,
                    'target'        => $target,
                    'target_type'   => $target_type,
                    'receiver'      => $uid,
                    'receiver_type' => $receiver_type,
                    'create_time'   => time(),
                ];
                Db::name('admin_message')->insert($data);
            }
            return self::createReturn(true, null, '操作成功');
        }
        return self::createReturn(false, null, '操作失败');
    }


    /**
     * 更新后台消息中心
     * @param $id
     * @param array $data
     * @return array
     * @throws \think\db\exception\DbException
     */
    static function updateAdminMessage($id, $data = [])
    {
        $res = Db::name('admin_message')->where('id', $id)->update($data);
        if ($res) {
            return self::createReturn(true, null, '操作成功');
        }
        return self::createReturn(false, null, '操作失败');
    }

    /**
     * 阅读消息（支持批量）
     * @param array $ids
     * @param $receiver
     * @return array
     * @throws \think\db\exception\DbException
     */
    static function readAdminMessage(array $ids, $receiver)
    {
        $_ids = join(',', $ids);
        $res = Db::name('admin_message')->where([
            ['id', 'in', $_ids],
            ['receiver', '=', $receiver],
        ])->update([
            'read_status' => AdminMessageModel::READ_STATUS_READED,
            'read_time'   => time()
        ]);
        if ($res) {
            return self::createReturn(true, null, '操作成功');
        }
        return self::createReturn(false, null, '操作失败');
    }

    /**
     * 已读所有
     * @param $receiver
     * @param string $type
     * @return array
     * @throws \think\db\exception\DbException
     */
    static function readAllAdminMessage($receiver, $type = '')
    {
        $where = [
            ['receiver', '=', $receiver],
            ['read_status', '=', AdminMessageModel::READ_STATUS_UNREAD]
        ];
        if (!empty($type)) {
            $where [] = ['type', '=', $type];
        }
        $res = Db::name('admin_message')->where($where)->update([
            'read_status' => AdminMessageModel::READ_STATUS_READED,
            'read_time'   => time()
        ]);
        if ($res) {
            return self::createReturn(true, null, '操作成功');
        }
        return self::createReturn(false, null, '操作失败');
    }


    /**
     * 删除消息
     *
     * @param $id
     *
     * @return array
     * @throws \think\db\exception\DbException
     */
    static function deleteAdminMessageById($id)
    {
        Db::name('admin_message')->where('id', $id)->delete();
        return self::createReturn(true, null, '操作成功');
    }
}
