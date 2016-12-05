<?php
namespace Shop\Controller;
use Common\Controller\AdminBase;
use Shop\Util\AjaxPage;
use Shop\Util\Page;
use Shop\Logic\ShopUsersLogic;
class UserController extends AdminBase{
    public function index(){
        $this->display();
    }
     /**
     * 会员列表
     */
    public function ajaxindex(){
        // 搜索条件
        $condition = array();
        I('mobile') ? $condition['mobile'] = array('like',"%".I('mobile')."%") : false;
        I('email') ? $condition['email'] =  array('like',"%".I('email')."%")  : false;
        $sort_order = I('order_by','userid').' '.I('sort','desc');
               
        $model = M('ShopUsers');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        //  搜索条件下 分页赋值
        foreach($condition as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        
        $userList = $model->where($condition)->order($sort_order)->limit($Page->firstRow.','.$Page->listRows)->select();
                
        $user_id_arr = get_arr_column($userList, 'userid');
        if(!empty($user_id_arr))
        {
            $first_leader = M('ShopUsers')->query("select first_leader,count(1) as count  from __PREFIX__shop_users where first_leader in(".  implode(',', $user_id_arr).")  group by first_leader");
            $first_leader = convert_arr_key($first_leader,'first_leader');
            
            $second_leader = M('ShopUsers')->query("select second_leader,count(1) as count  from __PREFIX__shop_users where second_leader in(".  implode(',', $user_id_arr).")  group by second_leader");
            $second_leader = convert_arr_key($second_leader,'second_leader');            
            
            $third_leader = M('ShopUsers')->query("select third_leader,count(1) as count  from __PREFIX__shop_users where third_leader in(".  implode(',', $user_id_arr).")  group by third_leader");
            $third_leader = convert_arr_key($third_leader,'third_leader');            
        }
        $this->assign('first_leader',$first_leader);
        $this->assign('second_leader',$second_leader);
        $this->assign('third_leader',$third_leader);                                
        $show = $Page->show();
        $this->assign('userList',$userList);
        $this->assign('level',M('user_level')->getField('level_id,level_name'));
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    /**
     * 会员详细信息查看
     */
    public function detail(){
        $uid = I('get.id');
        $user = D('ShopUsers')->where(array('userid'=>$uid))->find();
        $member = D('Member')->where(array('userid'=>$uid))->find();
        if(!$user && !$member)
            exit($this->error('会员不存在'));
        if(IS_POST){
            //  会员信息编辑
            $password = I('post.password');
            $password2 = I('post.password2');
            if($password != '' && $password != $password2){
                exit($this->error('两次输入密码不同'));
            }
            if($password == '' || $password2 == ''){
                unset($_POST['password']);
            }else{
                $_POST['password'] = encrypt($_POST['password']);
                echo service("Passport")->userEdit($member['username'], '', $password, '', 1);
            }
            $row = M('ShopUsers')->where(array('userid'=>$uid))->save($_POST);
            if($row)
                exit($this->success('修改成功'));
            exit($this->error('未作内容修改或修改失败'));
        }
        
        $user['first_lower'] = M('ShopUsers')->where("first_leader = {$user['userid']}")->count();
        $user['second_lower'] = M('ShopUsers')->where("second_leader = {$user['userid']}")->count();
        $user['third_lower'] = M('ShopUsers')->where("third_leader = {$user['userid']}")->count();
 
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * 账户资金调节
     */
    public function account_edit(){
        $user_id = I('get.id');
        if(!$user_id > 0)
            $this->error("参数有误");
        if(IS_POST){
            //获取操作类型
            $m_op_type = I('post.money_act_type');
            $user_money = I('post.user_money');
            $user_money =  $m_op_type ? $user_money : 0-$user_money;

            $p_op_type = I('post.point_act_type');
            $pay_points = I('post.pay_points');
            $pay_points =  $p_op_type ? $pay_points : 0-$pay_points;

            $f_op_type = I('post.frozen_act_type');
            $frozen_money = I('post.frozen_money');
            $frozen_money =  $f_op_type ? $frozen_money : 0-$frozen_money;

            $desc = I('post.desc');
            if(!$desc)
                $this->error("请填写操作说明");
            if(accountLog($user_id,$user_money,$pay_points,$desc)){
                $this->success("操作成功",U("User/account_log",array('id'=>$user_id)));
            }else{
                $this->error("操作失败");
            }
            exit;
        }
        $this->assign('user_id',$user_id);
        $this->display();
    }
    /**
     * 搜索用户名
     */
    public function search_user()
    {
        $search_key = trim(I('search_key'));        
        if(strstr($search_key,'@'))    
        {
            $list = M('ShopUsers')->where(" email like '%$search_key%' ")->select();        
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['userid']}'>{$val['email']}</option>";
            }                        
        }
        else
        {
            $list = M('ShopUsers')->where(" mobile like '%$search_key%' ")->select();        
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['userid']}'>{$val['mobile']}</option>";
            }            
        } 
        exit;
    }
      
    public function add_user(){
    	if(IS_POST){
    		$data = I('post.');
			$user_obj = new ShopUsersLogic();
			$res = $user_obj->addUser($data);
			if($res['status'] == 1){
				$this->success('添加成功',U('User/index'));exit;
			}else{
				$this->error($res['msg'],U('User/index'));
			}
    	}
    	$this->display();
    }
      /**
     * 用户收货地址查看
     */
    public function address(){
        $uid = I('get.id');
        $lists = D('UserAddress')->where(array('userid'=>$uid))->select();
        // 获取省份
        $province = M('AreaProvince')->getField('id,areaname');
        //获取订单城市
        $city =  M('AreaCity')->where(array('level'=>2))->getField('id,areaname');
        //获取订单地区
        $district =  M('AreaDistrict')->where(array('level'=>3))->getField('id,areaname');
        $this->assign('lists',$lists);
        $this->assign('province',$province);
        $this->assign('city',$city);
        $this->assign('district',$district);
        $this->display();
    }
     /**
     *
     * @time 2016/08/31
     * @author dyr
     * 发送站内信
     */
    public function sendMessage()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $users = M('ShopUsers')->field('userid,nickname')->where(array('userid' => array('IN', $user_id_array)))->select();
        }
        $this->assign('users',$users);
        $this->display('send_message');
    }
    /**
     * 发送系统消息
     * @author dyr
     * @time  2016/09/01
     */
    public function doSendMessage()
    {
        $call_back = I('call_back');//回调方法
        $message = I('post.text');//内容
        $type = I('post.type', 0);//个体or全体
        $admin_id = session('admin_id');
        $users = I('post.user');//个体id
        $message = array(
            'admin_id' => $admin_id,
            'message' => $message,
            'category' => 0,
            'send_time' => time()
        );
        if ($type == 1) {
            //全体用户系统消息
            $message['type'] = 1;
            M('Message')->data($message)->add();
        } else {
            //个体消息
            $message['type'] = 0;
            if (!empty($users)) {
                $create_message_id = M('Message')->data($message)->add();
                foreach ($users as $key) {
                    M('user_message')->data(array('user_id' => $key, 'message_id' => $create_message_id, 'status' => 0, 'category' => 0))->add();
                }
            }
        }
        echo "<script>parent.{$call_back}(1);</script>";
        exit();
    }

    /**
     *
     * @time 2016/09/03
     * @author dyr
     * 发送邮件
     */
    public function sendMail()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $user_where = array(
                'user_id' => array('IN', $user_id_array),
                'email' => array('neq', '')
            );
            $users = M('ShopUsers')->field('userid,nickname,email')->where($user_where)->select();
        }
        $this->assign('smtp', tpCache('smtp'));
        $this->assign('users', $users);
        $this->display('send_mail');
    }
      /**
     * 删除会员
     */
    public function delete(){
        $uid = I('get.id');
        $row = M('ShopUsers')->where(array('user_id'=>$uid))->delete();
        if($row){
            $this->success('成功删除会员');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 账户资金记录
     */
    public function account_log(){
        $user_id = I('get.id');
        //获取类型
        $type = I('get.type');
        //获取记录总数
        $count = M('AccountLog')->where(array('userid'=>$user_id))->count();
        $page = new Page($count);
        $lists  = M('AccountLog')->where(array('userid'=>$user_id))->order('change_time desc')->limit($page->firstRow.','.$page->listRows)->select();

        $this->assign('user_id',$user_id);
        $this->assign('page',$page->show());
        $this->assign('lists',$lists);
        $this->display();
    }


}