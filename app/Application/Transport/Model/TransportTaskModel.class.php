<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Model;


use Common\Model\Model;

class TransportTaskModel extends Model {

    /**
     * 导入
     */
    const TYPE_IMPORT = 1;
    /**
     * 导出
     */
    const TYPE_EXPORT = 2;

    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('model', 'require', '关联模型不能为空！', 1),
    );
    protected $_auto = array(
        // 新增的时候把inputtime字段设置为当前时间
        array('inputtime', 'time', 1, 'function'),
    );

}