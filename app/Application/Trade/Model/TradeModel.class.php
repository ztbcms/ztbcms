<?php

// +----------------------------------------------------------------------
// | 自定义页面模型
// +----------------------------------------------------------------------

namespace Trade\Model;

use Common\Model\Model;

class TradeModel extends Model {
    const STATUS_VALID = 1;
    const STATUS_INVALID = 0;

    protected $_validate = array(
        array('type', 'require', '交易类型不能为空', 0),
        array('trade_no', 'require', '交易凭证不能为空', 0),
        array('userid', '0', '交易用户不能为空', 0,'notequal'),
    );

    /**
     * 获取账户余额
     */

    public function get_balance($userid) {
        $condition['userid'] = $userid;
        $condition['status'] = self::STATUS_VALID;
        $last_trade = $this->where($condition)->order('id desc')->find();
        return $last_trade ? $last_trade['balance'] : 0;
    }

    /**
     * 添加收入
     * @param money 收入金额
     * @param type 收入类型
     * @param trade_no 交易凭证
     * @param userid 所属用户id
     * @param detail 交易详情（选填）
     * @param status 交易状态（默认是1）
     */
    public function add_income($money, $type, $trade_no, $userid, $detail = null, $status = 1) {
        $condition['userid'] = $userid;
        $condition['status'] = self::STATUS_VALID;
        //找出最后一条有效的交易记录
        $last_trade = $this->where($condition)->order('id desc')->find();
        $data = array(
            'userid' => $userid,
            'parent_id' => $last_trade ? $last_trade['id'] : 0,
            'income' => $money,
            'pay' => 0,
            'balance' => $last_trade ? $last_trade['balance'] + $money : $money,
            'type' => $type,
            'trade_no' => $trade_no,
            'detail' => $detail ? $detail : '',
            'status' => $status,
            'create_time' => time(),
        );
        if ($this->create($data)) {
            return $this->add($data);
        } else {
            exit($User->getError());
        }
    }
    /**
     * 添加支出
     * @param money 支出金额
     * @param type 支出类型
     * @param trade_no 交易凭证
     * @param userid 所属用户id
     * @param detail 交易详情（选填）
     * @param status 交易状态（默认是1）
     */
    public function add_pay($money, $type, $trade_no, $userid, $detail = null, $status = 1) {
        $condition['userid'] = $userid;
        $condition['status'] = self::STATUS_VALID;
        //找出最后一条有效的交易记录
        $last_trade = $this->where($condition)->order('id desc')->find();
        $data = array(
            'userid' => $userid,
            'parent_id' => $last_trade ? $last_trade['id'] : 0,
            'income' => 0,
            'pay' => $money,
            'balance' => $last_trade ? $last_trade['balance'] - $money : $money,
            'type' => $type,
            'trade_no' => $trade_no,
            'detail' => $detail ? $detail : '',
            'status' => $status,
            'create_time' => time(),
        );
        if ($this->create($data)) {
            return $this->add($data);
        } else {
            exit($User->getError());
        }
    }
}