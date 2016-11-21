<?php
define('UPLOAD_PATH','d/file/');
/**
 * @param $arr
 * @param $key_name
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 
 */
function convert_arr_key($arr, $key_name)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[$val[$key_name]] = $val;        
	}
	return $arr2;
}
/**
 * @param $arr
 * @param $key_name
  * @param $key_name2
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 数组指定列为元素 的一个数组
 */
function get_id_val($arr, $key_name,$key_name2)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[$val[$key_name]] = $val[$key_name2];
	}
	return $arr2;
}


//php获取中文字符拼音首字母
function getFirstCharter($str){
      if(empty($str))
      {
            return '';          
      }
      $fchar=ord($str{0});
      if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
      $s1=iconv('UTF-8','gb2312',$str);
      $s2=iconv('gb2312','UTF-8',$s1);
      $s=$s2==$str?$s1:$str;
      $asc=ord($s{0})*256+ord($s{1})-65536;
     if($asc>=-20319&&$asc<=-20284) return 'A';
     if($asc>=-20283&&$asc<=-19776) return 'B';
     if($asc>=-19775&&$asc<=-19219) return 'C';
     if($asc>=-19218&&$asc<=-18711) return 'D';
     if($asc>=-18710&&$asc<=-18527) return 'E';
     if($asc>=-18526&&$asc<=-18240) return 'F';
     if($asc>=-18239&&$asc<=-17923) return 'G';
     if($asc>=-17922&&$asc<=-17418) return 'H';
     if($asc>=-17417&&$asc<=-16475) return 'J';
     if($asc>=-16474&&$asc<=-16213) return 'K';
     if($asc>=-16212&&$asc<=-15641) return 'L';
     if($asc>=-15640&&$asc<=-15166) return 'M';
     if($asc>=-15165&&$asc<=-14923) return 'N';
     if($asc>=-14922&&$asc<=-14915) return 'O';
     if($asc>=-14914&&$asc<=-14631) return 'P';
     if($asc>=-14630&&$asc<=-14150) return 'Q';
     if($asc>=-14149&&$asc<=-14091) return 'R';
     if($asc>=-14090&&$asc<=-13319) return 'S';
     if($asc>=-13318&&$asc<=-12839) return 'T';
     if($asc>=-12838&&$asc<=-12557) return 'W';
     if($asc>=-12556&&$asc<=-11848) return 'X';
     if($asc>=-11847&&$asc<=-11056) return 'Y';
     if($asc>=-11055&&$asc<=-10247) return 'Z';
     return null;
} 

/**
 *   实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length) {
      if(mb_strlen($string,'utf-8')>$length){
          $str = mb_substr($string, $start, $length,'utf-8');
          return $str.'...';
      }else{
          return $string;
      }
}

/**
 * 获取某个商品分类的 儿子 孙子  重子重孙 的 id
 * @param type $cat_id
 */
function getCatGrandson ($cat_id)
{
    $GLOBALS['catGrandson'] = array();
    $GLOBALS['category_id_arr'] = array();
    // 先把自己的id 保存起来
    $GLOBALS['catGrandson'][] = $cat_id;
    // 把整张表找出来
    $GLOBALS['category_id_arr'] = M('GoodsCategory')->cache(true,TPSHOP_CACHE_TIME)->getField('id,parent_id');
    // 先把所有儿子找出来
    $son_id_arr = M('GoodsCategory')->where("parent_id = $cat_id")->cache(true,TPSHOP_CACHE_TIME)->getField('id',true);
    foreach($son_id_arr as $k => $v)
    {
        getCatGrandson2($v);
    }
    return $GLOBALS['catGrandson'];
}

/**
 * 递归调用找到 重子重孙
 * @param type $cat_id
 */
function getCatGrandson2($cat_id)
{
    $GLOBALS['catGrandson'][] = $cat_id;
    foreach($GLOBALS['category_id_arr'] as $k => $v)
    {
        // 找到孙子
        if($v == $cat_id)
        {
            getCatGrandson2($k); // 继续找孙子
        }
    }
}


// 递归删除文件夹
function delFile($dir,$file_type='') {
	if(is_dir($dir)){
		$files = scandir($dir);
		//打开目录 //列出目录中的所有文件并去掉 . 和 ..
		foreach($files as $filename){
			if($filename!='.' && $filename!='..'){
				if(!is_dir($dir.'/'.$filename)){
					if(empty($file_type)){
						unlink($dir.'/'.$filename);
					}else{
						if(is_array($file_type)){
							//正则匹配指定文件
							if(preg_match($file_type[0],$filename)){
								unlink($dir.'/'.$filename);
							}
						}else{
							//指定包含某些字符串的文件
							if(false!=stristr($filename,$file_type)){
								unlink($dir.'/'.$filename);
							}
						}
					}
				}else{
					delFile($dir.'/'.$filename);
					rmdir($dir.'/'.$filename);
				}
			}
		}
	}else{
		if(file_exists($dir)) unlink($dir);
	}
}
/**
 * 刷新商品库存, 如果商品有设置规格库存, 则商品总库存 等于 所有规格库存相加
 * @param type $goods_id  商品id
 */
function refresh_stock($goods_id){
    $count = M("SpecGoodsPrice")->where("goods_id = $goods_id")->count();
    if($count == 0) return false; // 没有使用规格方式 没必要更改总库存

    $store_count = M("SpecGoodsPrice")->where("goods_id = $goods_id")->sum('store_count');
    M("Goods")->where("goods_id = $goods_id")->save(array('store_count'=>$store_count)); // 更新商品的总库存
}

/**
 * 多个数组的笛卡尔积
*
* @param unknown_type $data
*/
function combineDika() {
	$data = func_get_args();
	$data = current($data);
	$cnt = count($data);
	$result = array();
    $arr1 = array_shift($data);
	foreach($arr1 as $key=>$item) 
	{
		$result[] = array($item);
	}		

	foreach($data as $key=>$item) 
	{                                
		$result = combineArray($result,$item);
	}
	return $result;
}

/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
*/
function combineArray($arr1,$arr2) {		 
	$result = array();
	foreach ($arr1 as $item1) 
	{
		foreach ($arr2 as $item2) 
		{
			$temp = $item1;
			$temp[] = $item2;
			$result[] = $temp;
		}
	}
	return $result;
}
/**
 * 获取数组中的某一列
 * @param type $arr 数组
 * @param type $key_name  列名
 * @return type  返回那一列的数组
 */
function get_arr_column($arr, $key_name)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[] = $val[$key_name];        
	}
	return $arr2;
}

/**
 * 获取商品库存
 * @param type $goods_id 商品id
 * @param type $key  库存 key
 */
function getGoodNum($goods_id,$key)
{
    if(!empty($key))
        return  M("SpecGoodsPrice")->where("goods_id = $goods_id and `key` = '$key'")->getField('store_count');
    else
        return  M("Goods")->where("goods_id = $goods_id")->getField('store_count');
}

/**
 * 计算订单金额
 * @param type $user_id  用户id
 * @param type $order_goods  购买的商品
 * @param type $shipping  物流code
 * @param type $shipping_price 物流费用, 如果传递了物流费用 就不在计算物流费
 * @param type $province  省份
 * @param type $city 城市
 * @param type $district 县
 * @param type $pay_points 积分
 * @param type $user_money 余额
 * @param type $coupon_id  优惠券
 * @param type $couponCode  优惠码
 */
 
function calculate_price($user_id=0,$order_goods,$shipping_code='',$shipping_price=0,$province=0,$city=0,$district=0,$pay_points=0,$user_money=0,$coupon_id=0,$couponCode='')
{    
    $cartLogic = new \Shop\Logic\CartLogic();               
    $user = M('ShopUsers')->where("userid = $user_id")->find();// 找出这个用户
    
    if(empty($order_goods)) 
        return array('status'=>-9,'msg'=>'商品列表不能为空','result'=>'');  
    
    $goods_id_arr = get_arr_column($order_goods,'goods_id');
    $goods_arr = M('goods')->where("goods_id in(".  implode(',',$goods_id_arr).")")->getField('goods_id,weight,market_price,is_free_shipping'); // 商品id 和重量对应的键值对
    
        foreach($order_goods as $key => $val)
        {       
            // 如果传递过来的商品列表没有定义会员价
            if(!array_key_exists('member_goods_price',$val))  
            {
                $user['discount'] = $user['discount'] ? $user['discount'] : 1; // 会员折扣 不能为 0
                $order_goods[$key]['member_goods_price'] = $val['member_goods_price'] = $val['goods_price'] * $user['discount'];
            }
			//如果商品不是包邮的
            if($goods_arr[$val['goods_id']]['is_free_shipping'] == 0)
	            $goods_weight += $goods_arr[$val['goods_id']]['weight'] * $val['goods_num']; //累积商品重量 每种商品的重量 * 数量
				
            $order_goods[$key]['goods_fee'] = $val['goods_num'] * $val['member_goods_price'];    // 小计            
            $order_goods[$key]['store_count']  = getGoodNum($val['goods_id'],$val['spec_key']); // 最多可购买的库存数量
            if($order_goods[$key]['store_count'] <= 0) 
                return array('status'=>-10,'msg'=>$order_goods[$key]['goods_name']."库存不足,请重新下单",'result'=>'');  
            
            $goods_price += $order_goods[$key]['goods_fee']; // 商品总价
            $cut_fee     += $val['goods_num'] * $val['market_price'] - $val['goods_num'] * $val['member_goods_price']; // 共节约
            $anum        += $val['goods_num']; // 购买数量
        }        
        
        // 优惠券处理操作
        $coupon_price = 0;
        if($coupon_id && $user_id)
        {
            $coupon_price = $cartLogic->getCouponMoney($user_id, $coupon_id,1); // 下拉框方式选择优惠券                    
        }        
        if($couponCode && $user_id)
        {                 
             $coupon_result = $cartLogic->getCouponMoneyByCode($couponCode,$goods_price); // 根据 优惠券 号码获取的优惠券             
             if($coupon_result['status'] < 0) 
               return $coupon_result;
             $coupon_price = $coupon_result['result'];            
        }
        // 处理物流
        // if($shipping_price == 0)
        // {
        //     $shipping_price = $cartLogic->cart_freight2($shipping_code,$province,$city,$district,$goods_weight);        
        //     $freight_free = tpCache('shopping.freight_free'); // 全场满多少免运费
        //     if($freight_free > 0 && $goods_price >= $freight_free)
        //        $shipping_price = 0;               
        // }
        
        if($pay_points && ($pay_points > $user['pay_points']))
            return array('status'=>-5,'msg'=>"你的账户可用积分为:".$user['pay_points'],'result'=>''); // 返回结果状态                
        if($user_money  && ($user_money > $user['user_money']))
            return array('status'=>-6,'msg'=>"你的账户可用余额为:".$user['user_money'],'result'=>''); // 返回结果状态

       $order_amount = $goods_price + $shipping_price - $coupon_price; // 应付金额 = 商品价格 + 物流费 - 优惠券
       
       $pay_points = ($pay_points / tpCache('shopping.point_rate')); // 积分支付 100 积分等于 1块钱                              
       $pay_points = ($pay_points > $order_amount) ? $order_amount : $pay_points; // 假设应付 1块钱 而用户输入了 200 积分 2块钱, 那么就让 $pay_points = 1块钱 等同于强制让用户输入1块钱               
       $order_amount = $order_amount - $pay_points; //  积分抵消应付金额       
      
       $user_money = ($user_money > $order_amount) ? $order_amount : $user_money;  // 余额支付原理等同于积分
       $order_amount = $order_amount - $user_money; //  余额支付抵应付金额
      
       $total_amount = $goods_price + $shipping_price;
           //订单总价  应付金额  物流费  商品总价 节约金额 共多少件商品 积分  余额  优惠券
        $result = array(
            'total_amount'      => $total_amount, // 商品总价
            'order_amount'      => $order_amount, // 应付金额
            'shipping_price'    => $shipping_price, // 物流费
            'goods_price'       => $goods_price, // 商品总价
            'cut_fee'           => $cut_fee, // 共节约多少钱
            'anum'              => $anum, // 商品总共数量
            'integral_money'    => $pay_points,  // 积分抵消金额
            'user_money'        => $user_money, // 使用余额
            'coupon_price'      => $coupon_price,// 优惠券抵消金额
            'order_goods'       => $order_goods, // 商品列表 多加几个字段原样返回
        );        
    return array('status'=>1,'msg'=>"计算价钱成功",'result'=>$result); // 返回结果状态
}
/**
 * 获取缓存或者更新缓存
 * @param string $config_key 缓存文件名称
 * @param array $data 缓存数据  array('k1'=>'v1','k2'=>'v3')
 * @return array or string or bool
 */
function tpCache($config_key,$data = array()){
    $param = explode('.', $config_key);
    if(empty($data)){
        //如$config_key=shop_info则获取网站信息数组
        //如$config_key=shop_info.logo则获取网站logo字符串
        $config = F($param[0],'',TEMP_PATH);//直接获取缓存文件
        if(empty($config)){
            //缓存文件不存在就读取数据库
            $res = D('ShopConfig')->where("inc_type='$param[0]'")->select();
            if($res){
                foreach($res as $k=>$val){
                    $config[$val['name']] = $val['value'];
                }
                F($param[0],$config,TEMP_PATH);
            }
        }
        if(count($param)>1){
            return $config[$param[1]];
        }else{
            return $config;
        }
    }else{
        //更新缓存
        $result =  D('ShopConfig')->where("inc_type='$param[0]'")->select();
        if($result){
            foreach($result as $val){
                $temp[$val['name']] = $val['value'];
            }
            foreach ($data as $k=>$v){
                $newArr = array('name'=>$k,'value'=>trim($v),'inc_type'=>$param[0]);
                if(!isset($temp[$k])){
                    M('ShopConfig')->add($newArr);//新key数据插入数据库
                }else{
                    if($v!=$temp[$k])
                        M('ShopConfig')->where("name='$k'")->save($newArr);//缓存key存在且值有变更新此项
                }
            }
            //更新后的数据库记录
            $newRes = D('ShopConfig')->where("inc_type='$param[0]'")->select();
            foreach ($newRes as $rs){
                $newData[$rs['name']] = $rs['value'];
            }
        }else{
            foreach($data as $k=>$v){
                $newArr[] = array('name'=>$k,'value'=>trim($v),'inc_type'=>$param[0]);
            }
            M('ShopConfig')->addAll($newArr);
            $newData = $data;
        }
        return F($param[0],$newData,TEMP_PATH);
    }
}
/**
 * 根据id获取地区名字
 * @param $regionId id
 */
function getRegionName($regionId,$level=1){
	 if(I('get.level')==1){
            $data = M('AreaProvince')->where("id=$regionId")->select();
        }elseif(I('get.level')==2){
            $data = M('AreaCity')->where("id=$regionId")->select();
        }else{
            $data = M('AreaDistrict')->where("id=$regionId")->select();
    }
    return $data['areaname'];
}

/**
 * 支付完成修改订单
 * $order_sn 订单号
 * $pay_status 默认1 为已支付
 */
function update_pay_status($order_sn,$pay_status = 1)
{
	if(stripos($order_sn,'recharge') !== false){
		//用户在线充值
		$count = M('recharge')->where("order_sn = '$order_sn' and pay_status = 0")->count();   // 看看有没已经处理过这笔订单  支付宝返回不重复处理操作
		if($count == 0) return false;
		$order = M('recharge')->where("order_sn = '$order_sn'")->find();
		M('recharge')->where("order_sn = '$order_sn'")->save(array('pay_status'=>1,'pay_time'=>time()));
		accountLog($order['user_id'],$order['account'],0,'会员在线充值');
	}else{
		// 如果这笔订单已经处理过了
		$count = M('order')->where("order_sn = '$order_sn' and pay_status = 0")->count();   // 看看有没已经处理过这笔订单  支付宝返回不重复处理操作
		if($count == 0) return false;
		// 找出对应的订单
		$order = M('order')->where("order_sn = '$order_sn'")->find();
		// 修改支付状态  已支付
		M('order')->where("order_sn = '$order_sn'")->save(array('pay_status'=>1,'pay_time'=>time()));
		// 减少对应商品的库存
		minus_stock($order['order_id']);
		// 给他升级, 根据order表查看消费记录 给他会员等级升级 修改他的折扣 和 总金额
		update_user_level($order['user_id']);
		// 记录订单操作日志
		logOrder($order['order_id'],'订单付款成功','付款成功',$order['user_id']);
		//分销设置
		M('rebate_log')->where("order_id = {$order['order_id']}")->save(array('status'=>1));
		// 成为分销商条件
		$distribut_condition = tpCache('distribut.condition');
		if($distribut_condition == 1)  // 购买商品付款才可以成为分销商
			M('ShopUsers')->where("userid = {$order['user_id']}")->save(array('is_distribut'=>1));
	}

}

/**
 * 根据 order_goods 表扣除商品库存
 * @param type $order_id  订单id
 */
function minus_stock($order_id){
    $orderGoodsArr = M('OrderGoods')->where("order_id = $order_id")->select();
    foreach($orderGoodsArr as $key => $val)
    {
        // 有选择规格的商品
        if(!empty($val['spec_key']))
        {   // 先到规格表里面扣除数量 再重新刷新一个 这件商品的总数量
            M('SpecGoodsPrice')->where("goods_id = {$val['goods_id']} and `key` = '{$val['spec_key']}'")->setDec('store_count',$val['goods_num']);
            refresh_stock($val['goods_id']);
        }else{
            M('Goods')->where("goods_id = {$val['goods_id']}")->setDec('store_count',$val['goods_num']); // 直接扣除商品总数量
        }
        M('Goods')->where("goods_id = {$val['goods_id']}")->setInc('sales_sum',$val['goods_num']); // 增加商品销售量
        //更新活动商品购买量
        if($val['prom_type']==1 || $val['prom_type']==2){
        	$prom = get_goods_promotion($val['goods_id']);
        	if($prom['is_end']==0){
        		$tb = $val['prom_type']==1 ? 'flash_sale' : 'group_buy';
        		M($tb)->where("id=".$val['prom_id'])->setInc('buy_num',$val['goods_num']);
        		M($tb)->where("id=".$val['prom_id'])->setInc('order_num');
        	}
        }
    }
}
/**
 * 更新会员等级,折扣，消费总额
 * @param $user_id  用户ID
 * @return boolean
 */
function update_user_level($user_id){
    $level_info = M('user_level')->order('level_id')->select();
    $total_amount = M('order')->where("user_id=$user_id AND pay_status=1 and order_status not in (3,5)")->sum('order_amount');
    if($level_info){
    	foreach($level_info as $k=>$v){
    		if($total_amount >= $v['amount']){
    			$level = $level_info[$k]['level_id'];
    			$discount = $level_info[$k]['discount']/100;
    		}
    	}
    	$user = session('user');
    	$updata['total_amount'] = $total_amount;//更新累计修复额度
    	//累计额度达到新等级，更新会员折扣
    	if(isset($level) && $level>$user['level']){
    		$updata['level'] = $level;
    		$updata['discount'] = $discount;	
    	}
    	M('ShopUsers')->where("userid=$user_id")->save($updata);
    }
}

/**
 * 订单操作日志
 * 参数示例
 * @param type $order_id  订单id
 * @param type $action_note 操作备注
 * @param type $status_desc 操作状态  提交订单, 付款成功, 取消, 等待收货, 完成
 * @param type $user_id  用户id 默认为管理员
 * @return boolean
 */
function logOrder($order_id,$action_note,$status_desc,$user_id = 0)
{
    $status_desc_arr = array('提交订单', '付款成功', '取消', '等待收货', '完成','退货');
    // if(!in_array($status_desc, $status_desc_arr))
    // return false;

    $order = M('order')->where("order_id = $order_id")->find();
    $action_info = array(
        'order_id'        =>$order_id,
        'action_user'     =>$user_id,
        'order_status'    =>$order['order_status'],
        'shipping_status' =>$order['shipping_status'],
        'pay_status'      =>$order['pay_status'],
        'action_note'     => $action_note,
        'status_desc'     =>$status_desc, //''
        'log_time'        =>time(),
    );
    return M('order_action')->add($action_info);
}


/**
 * 记录帐户变动
 * @param   int     $user_id        用户id
 * @param   float   $user_money     可用余额变动
 * @param   int     $pay_points     消费积分变动
 * @param   string  $desc    变动说明
 * @param   float   distribut_money 分佣金额
 * @return  bool
 */
function accountLog($user_id, $user_money = 0,$pay_points = 0, $desc = '',$distribut_money = 0){
    /* 插入帐户变动记录 */
    $account_log = array(
        'userid'       => $user_id,
        'user_money'    => $user_money,
        'pay_points'    => $pay_points,
        'change_time'   => time(),
        'desc'   => $desc,
    );
    /* 更新用户信息 */
    $sql = "UPDATE __PREFIX__shop_users SET user_money = user_money + $user_money," .
        " pay_points = pay_points + $pay_points, distribut_money = distribut_money + $distribut_money WHERE userid = $user_id";
    if( D('ShopUsers')->execute($sql)){
    	M('account_log')->add($account_log);
        return true;
    }else{
        return false;
    }
}

    /**
     * 订单确认收货
     * @param $id   订单id
     */
    function confirm_order($id,$user_id = 0){
        
        $where = "order_id = $id";
        $user_id && $where .= " and user_id = $user_id ";
        
        $order = M('order')->where($where)->find();
        if($order['order_status'] != 1)
            return array('status'=>-1,'msg'=>'该订单不能收货确认');
        
        $data['order_status'] = 2; // 已收货        
        $data['pay_status'] = 1; // 已付款        
        $data['confirm_time'] = time(); // 收货确认时间
        if($order['pay_code'] == 'cod'){
        	$data['pay_time'] = time();
        }
        $row = M('order')->where(array('order_id'=>$id))->save($data);
        if(!$row)        
            return array('status'=>-3,'msg'=>'操作失败');
        //暂时不触发，后面可以用hook来执行
        // order_give($order);// 调用送礼物方法, 给下单这个人赠送相应的礼物
        
        //分销设置
        M('rebate_log')->where("order_id = $id")->save(array('status'=>2,'confirm'=>time()));
               
        return array('status'=>1,'msg'=>'操作成功');
    }
    /**
 * 给订单送券送积分 送东西
 */
function order_give($order)
{
	$order_goods = M('order_goods')->where("order_id=".$order['order_id'])->cache(true)->select();
	//查找购买商品送优惠券活动
	foreach ($order_goods as $val)
    {
		if($val['prom_type'] == 3)
        {
			$prom = M('prom_goods')->where('type=3 and id='.$val['prom_id'])->find();
			if($prom){
				$coupon = M('coupon')->where("id=".$prom['expression'])->find();//查找优惠券模板
				if($coupon && $coupon['createnum']>0){					                                        
                    $remain = $coupon['createnum'] - $coupon['send_num'];//剩余派发量
                    if($remain > 0)                                            
                    {
                        $data = array('cid'=>$coupon['id'],'type'=>$coupon['type'],'uid'=>$order['user_id'],'send_time'=>time());
                        M('coupon_list')->add($data);       
                        M('Coupon')->where("id = {$coupon['id']}")->setInc('send_num'); // 优惠券领取数量加一
                    }
				}
		 	}
		 }
	}
	
	//查找订单满额送优惠券活动
	$pay_time = $order['pay_time'];
	$prom = M('prom_order')->where("type>1 and end_time>$pay_time and start_time<$pay_time and money<=".$order['order_amount'])->order('money desc')->find();
	if($prom){
		if($prom['type']==3){
			$coupon = M('coupon')->where("id=".$prom['expression'])->find();//查找优惠券模板
			if($coupon){
				if($coupon['createnum']>0){
					$remain = $coupon['createnum'] - $coupon['send_num'];//剩余派发量
                    if($remain > 0)
                    {
                       $data = array('cid'=>$coupon['id'],'type'=>$coupon['type'],'uid'=>$order['user_id'],'send_time'=>time());
                       M('coupon_list')->add($data);           
                       M('Coupon')->where("id = {$coupon['id']}")->setInc('send_num'); // 优惠券领取数量加一
                    }				
				}
			}
		}else if($prom['type']==2){
			accountLog($order['user_id'], 0 , $prom['expression'] ,"订单活动赠送积分");
		}
	}
    $points = M('order_goods')->where("order_id = {$order[order_id]}")->sum("give_integral * goods_num");
    $points && accountLog($order['user_id'], 0,$points,"下单赠送积分");
}

/**
 * 导出excel
 * @param $strTable	表格内容
 * @param $filename 文件名
 */
function downloadExcel($strTable,$filename)
{
	header("Content-type: application/vnd.ms-excel");
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=".$filename."_".date('Y-m-d').".xls");
	header('Expires:0');
	header('Pragma:public');
	echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
}
function encrypt($str){
	return md5(C("AUTH_CODE").$str);
}

/**
*  面包屑导航  用于前台商品
 * @param type $id 商品id  或者是 商品分类id
 * @param type $type 默认0是传递商品分类id  id 也可以传递 商品id type则为1
 */
function navigate_goods($id,$type = 0)
{
    $cat_id = $id; //
    // 如果传递过来的是
    if($type == 1){
        $cat_id = M('goods')->where("goods_id = $id")->getField('cat_id');
    }
    $categoryList = M('GoodsCategory')->getField("id,name,parent_id");

    // 第一个先装起来
    $arr[$cat_id] = $categoryList[$cat_id]['name'];
    while (true)
    {
        $cat_id = $categoryList[$cat_id]['parent_id'];
        if($cat_id > 0)
            $arr[$cat_id] = $categoryList[$cat_id]['name'];
        else
            break;
    }
    $arr = array_reverse($arr,true);
    return $arr;
}
/**
 * 查看某个用户购物车中商品的数量
 * @param type $user_id
 * @param type $session_id
 * @return type 购买数量
 */
function cart_goods_num($user_id = 0,$session_id = '')
{
    $where = " session_id = '$session_id' ";
    $user_id && $where .= " or user_id = $user_id ";
    // 查找购物车数量
    $cart_count =  M('Cart')->where($where)->sum('goods_num');
    $cart_count = $cart_count ? $cart_count : 0;
    return $cart_count;
}