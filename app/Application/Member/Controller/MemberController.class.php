<?php

// +----------------------------------------------------------------------
// | 会员组管理
// +----------------------------------------------------------------------

namespace Member\Controller;

use Common\Controller\AdminBase;
use Member\BehaviorParam\MemberBehaviorParam;
use Think\Hook;

class MemberController extends AdminBase {

    //会员用户组缓存
    protected $groupCache = array();
    //会员模型
    protected $groupsModel = array();
    //会员数据模型
    protected $member = null;

    //初始化
    protected function _initialize() {
        parent::_initialize();
        $this->groupCache = cache("Member_group");
        $this->groupsModel = cache("Model_Member");
        $this->member = D('Member/Member');
    }

    //会员管理首页
    public function index() {
        $search = I("get.search", null);
        $where = [];

        foreach ($this->groupCache as $key => $g) {
            if (in_array($g['groupid'], array(8, 1, 7))) {
                continue;
            }
            $groupCache[$key]['id'] = $g['groupid'];
            $groupCache[$key]['label'] = $g['name'];
        }
        $groupCache1 = [];
        foreach ($groupCache as $groupCache_v){
            $groupCache1[] =  $groupCache_v;
        }
        //会员模型
        foreach ($this->groupsModel as $m) {
            $groupsModel[$m['modelid']] = $m['name'];
        }
        $groupsModel = json_encode($groupsModel);
        $groupCache = json_encode($groupCache1);
        $this->assign('groupsModel', $groupsModel);
        $this->assign('groupCache1', $groupCache);
        $this->display();
    }

    //获取会员模型
    public function getGroupsModel(){
        $res = D('model')->field('modelid as id ,name as label')->select();
        return $this->ajaxReturn(self::createReturn(true,$res));
    }

    //获取会员列表
    public function getMemberUser() {
        $search = I("get.search", null);
        $where = [];
        //注册时间段
        if (isset($_GET['input_date'])) {
            $start_time = $_GET['input_date'][0] ? $_GET['input_date'][0] : '';
            $end_time = $_GET['input_date'][1] ? $_GET['input_date'][1] : date('Y-m-d', time());
        }
        //开始时间
        $where_start_time = strtotime($start_time) ? strtotime($start_time) : 0;
        //结束时间
        $where_end_time = strtotime($end_time) ? (strtotime($end_time) + 86400) : 0;
        //开始时间大于结束时间，置换变量
        if ($where_start_time > $where_end_time) {
            $tmp = $where_start_time;
            $where_start_time = $where_end_time;
            $where_end_time = $tmp;
            $tmptime = $start_time;
            $start_time = $end_time;
            $end_time = $tmptime;
            unset($tmp, $tmptime);
        }
        //时间范围
        if ($where_start_time) {
            $where['regdate'] = array('between', array($where_start_time, $where_end_time));
        }

        $islock = I('get.islock'); //是否锁定
        $checked = I('get.checked'); //是否审核
        $type1 = I('get.type1'); //搜索的字段  用户名。昵称
        $type2 = I('get.type2'); //大于 等于
        $title = I('get.title'); //关键词

        if ($islock != '') {
            $where['islock'] = array('eq', $islock);
        }
        if ($checked != '') {
            $where['checked'] = array('eq', $checked);
        }
        if ($title != '') {
            $where[$type1] = array($type2, $title);
        }

        $page = I('get.page', 1);
        $limit = I('get.limit', 20);
        $count = $this->member->where($where)->count();
        $data = $this->member->where($where)
            ->page($page)
            ->limit($limit)
            ->order(array("userid" => "DESC"))->select();
        //总页数
        $total_page = ceil($count / $limit);

        foreach ($this->groupCache as $g) {
            $groupCache[$g['groupid']] = $g['name'];
        }
        foreach ($this->groupsModel as $m) {
            $groupsModel[$m['modelid']] = $m['name'];
        }
        return $this->ajaxReturn(self::createReturnList(true, $data,$page,$limit,$count, $total_page));
    }

    //添加会员
    public function add() {
        if (IS_POST) {
            $post = I('post.');
            $info = $this->member->token(false)->create($post);
            if ($info) {
                //vip过期时间
                $info['overduedate'] = strtotime($post['overduedate']);
                $userid = service("Passport")->userRegister($info['username'], $info['password'], $info['email']);
                if ($userid > 0) {
                    $memberinfo = service("Passport")->getLocalUser((int)$userid);
                    $info['username'] = $memberinfo['username'];
                    $info['password'] = $memberinfo['password'];
                    $info['email'] = $memberinfo['email'];
                    if (false !== $this->member->where(array('userid' => $memberinfo['userid']))->save($info)) {
                        //添加附表信息
                        $this->addMemberData($memberinfo['userid'], $post['modelid'], $post['info']);
                        $this->success("添加会员成功！", U("Member/index"));
                    } else {
                        service("Passport")->userDelete($memberinfo['userid']);
                        $this->error("添加会员失败！");
                    }
                } else {
                    $this->error($this->member->getError($userid));
                }
            } else {
                $this->error($this->member->getError());
            }
        } else {
            foreach ($this->groupCache as $g) {
                if (in_array($g['groupid'], array(8, 1, 7))) {
                    continue;
                }
                $groupCache[$g['groupid']] = $g['name'];
            }
            foreach ($this->groupsModel as $m) {
                $groupsModel[$m['modelid']] = $m['name'];
            }
            $this->assign('groupCache', $groupCache);
            $this->assign('groupsModel', $groupsModel);
            $this->display();
        }
    }

    //修改会员
    public function edit() {
        if (IS_POST) {
            $userid = I('post.userid', 0, 'intval');
            $post = I('post.');
            $info = I('post.info');
            $modelid = I('post.modelid', 0, 'intval');
            $data = $this->member->create($post);
            if ($data) {
                $data['overduedate'] = strtotime($data['overduedate']);
                //获取用户信息
                $userinfo = service("Passport")->getLocalUser($userid);
                if (empty($userinfo)) {
                    $this->error('该会员不存在！');
                }
                $ContentModel = \Content\Model\ContentModel::getInstance($modelid);
                if ($userinfo['modelid'] == $modelid && $info) {
                    //详细信息验证
                    $content_input = new \content_input($modelid);
                    $inputinfo = $content_input->get($info, 2);
                    if ($inputinfo) {
                        //数据验证
                        $inputinfo = $ContentModel->token(false)->create($inputinfo, 2);
                        if (false == $inputinfo) {
                            $ContentModel->tokenRecovery($post);
                            $this->error($ContentModel->getError());
                        }
                    } else {
                        $ContentModel->tokenRecovery($post);
                        $this->error($content_input->getError());
                    }
                    //检查详细信息是否已经添加过
                    if ($ContentModel->where(array("userid" => $userid))->find()) {
                        $ContentModel->where(array("userid" => $userid))->save($inputinfo);
                    } else {
                        $inputinfo['userid'] = $userid;
                        $ContentModel->add($inputinfo);
                    }
                }
                //判断是否需要删除头像
                if (I('post.delavatar')) {
                    service("Passport")->userDeleteAvatar($userinfo['userid']);
                }
                //修改基本资料
                if ($userinfo['username'] != $data['username'] || !empty($data['password']) || $userinfo['email'] != $data['email']) {
                    $edit = service("Passport")->userEdit($data['username'], '', $data['password'], $data['email'], 1);
                    if ($edit < 0) {
                        $this->error($this->member->getError($edit));
                    }
                }
                unset($data['username'], $data['password'], $data['email']);
                //更新除基本资料外的其他信息
                if (false === $this->member->where(array('userid' => $userid))->save($data)) {
                    $this->error('更新失败！');
                }
                $this->success("更新成功！", U("Member/index"));
            } else {
                $this->error($this->member->getError());
            }
        } else {
            $userid = I('get.userid', 0, 'intval');
            $modelid = I('get.modelid', 0, 'intval');
            //主表
            $data = $this->member->where(array("userid" => $userid))->find();
            if (empty($data)) {
                $this->error("该会员不存在！");
            }
            if ($modelid) {
                if (!$this->groupsModel[$modelid]) {
                    $this->error("该模型不存在！");
                }
            } else {
                $modelid = $data['modelid'];
            }
            //会员模型数据表名
            $tablename = $this->groupsModel[$modelid]['tablename'];
            //相应会员模型数据
            $modeldata = null;
            if (!empty($tablename)) {
                $modeldata = M(ucwords($tablename))->where(array("userid" => $userid))->find();
            }
            if (!is_array($modeldata)) {
                $modeldata = array();
            }
            $data = array_merge($data, $modeldata);
            $content_form = new \content_form($modelid);
            $data['modelid'] = $modelid;
            //字段内容
            $forminfos = $content_form->get($data);
            //js提示
            $formValidator = $content_form->formValidator;

            foreach ($this->groupCache as $g) {
                if (in_array($g['groupid'], array(8, 1, 7))) {
                    continue;
                }
                $groupCache[$g['groupid']] = $g['name'];
            }
            foreach ($this->groupsModel as $m) {
                $groupsModel[$m['modelid']] = $m['name'];
            }
            $this->assign('groupCache', $groupCache);
            $this->assign('groupsModel', $groupsModel);
            $this->assign("forminfos", $forminfos);
            $this->assign("formValidator", $formValidator);
            $this->assign("data", $data);
            $this->display();
        }
    }

    //删除会员
    public function delete() {
        if (IS_POST) {
            $userid = I('post.userid');
            if (!$userid) {
                $this->error("请选择需要删除的会员！");
            }
            $connect = M("Connect");
            foreach ($userid as $uid) {
                $info = $this->member->where(array("userid" => $uid))->find();
                if (!empty($info)) {
                    //删除会员信息，且删除投稿相关
                    if (service("Passport")->userDelete($uid)) {
                        $connect->where(array("uid" => $uid))->delete();
                    }
                }
            }
            $this->success("删除成功！");
        }
    }

    //锁定会员
    public function lock() {
        if (IS_POST) {
            $userid = I('post.userid');
            if (!$userid) {
                $this->error("请选择需要锁定的会员！");
            }
            $this->member->where(array("userid" => array('IN', $userid)))->save(array("islock" => 1));
            $this->success("锁定成功！");
        }
    }

    //解除锁定会员
    public function unlock() {
        if (IS_POST) {
            $userid = I('post.userid');
            if (!$userid) {
                $this->error("请选择需要解锁的会员！");
            }
            $this->member->where(array("userid" => array('IN', $userid)))->save(array("islock" => 0));
            $this->success("解锁成功！");
        }
    }

    //会员资料查看
    public function memberinfo(){
        $userid = I('get.userid', 0, 'intval');
        //主表
        $data = $this->member->where(array("userid" => $userid))->find();

        if (empty($data)) {
            $this->error("该会员不存在！");
        }
        if (!$data['modelid']) {
            echo '会员信息不完善！';
            exit();
        }
        $modelid = $data['modelid'];
        //相应会员模型数据
        $modeldata = \Content\Model\ContentModel::getInstance($modelid)->where(array("userid" => $userid))->find();

        $content_output = new \content_output($modelid);
        $output_data = $content_output->get($modeldata);
        $modelField = cache('ModelField');
        $Model_field = $modelField[$modelid];
        foreach ($this->groupCache as $g) {
            $groupCache[$g['groupid']] = $g['name'];
        }
        foreach ($this->groupsModel as $m) {
            $groupsModel[$m['modelid']] = $m['name'];
        }
        $this->assign('groupCache', json_encode($groupCache));
        $this->assign('groupsModel',json_encode($groupsModel));
        $this->assign("output_data", json_encode($output_data));
        $this->assign("Model_field", json_encode($Model_field));
        $this->assign('userinfo',json_encode($data));
        $this->display();
    }

    //获取会员资料api
    public function memberinfoApi(){
        $userid = I('get.userid', 0, 'intval');
        //主表
        $data = $this->member->where(array("userid" => $userid))->find();
        if (empty($data)) {
            $this->error("该会员不存在！");
        }
        if (!$data['modelid']) {
            echo '会员信息不完善！';
            exit();
        }
        $modelid = $data['modelid'];
        //相应会员模型数据
        $modeldata = \Content\Model\ContentModel::getInstance($modelid)->where(array("userid" => $userid))->find();
        $content_output = new \content_output($modelid);
        $output_data = $content_output->get($modeldata);
        $modelField = cache('ModelField');
        $Model_field = $modelField[$modelid];
        foreach ($this->groupCache as $g) {
            $groupCache[$g['groupid']] = $g['name'];
        }
        foreach ($this->groupsModel as $m) {
            $groupsModel[$m['modelid']] = $m['name'];
        }
        $data['userinfo'] = $data;
        $data['groupCache'] = $groupCache;
        $data['groupsModel'] = $groupsModel;
        $data['output_data'] = $output_data;
        $data['Model_field'] = $Model_field;
        return $this->ajaxReturn(self::createReturn(true,$data,'获取成功'));
    }

    //审核会员
    public function userverify() {
        if (IS_POST) {
            $userid = $_POST['userid'];
            if (!$userid) {
                $this->error("请选择需要审核的会员！");
            }
            $members = $this->member->where(array("userid" => array('IN', $userid)))->select();
            foreach ($members as $val) {
                $res = $this->member->where(array("userid" => $val['userid']))->save(array("checked" => 1));
                if ($res) {
                    //更新成功触发，审核通过行为
                    Hook::listen('member_verify', MemberBehaviorParam::create(['userid' => $val['userid']]));
                }
            }
            $this->success("审核成功！");
        } else {
            $this->redirect('Member/Member/index',
                ['search' => 1, '_filter[2]' => 'checked', '_operator[2]' => 'EQ', '_value[2]' => 0,'checked'=>'0']);
        }
    }

    //取消审核会员
    public function userunverify() {
        if (IS_POST) {
            $userid = $_POST['userid'];
            if (!$userid) {
                $this->error("请选择需要取消审核的会员！");
            }
            $members = $this->member->where(array("userid" => array('IN', $userid)))->select();
            foreach ($members as $val) {
                $res = $this->member->where(array("userid" => $val['userid']))->save(array("checked" => 0));
                if ($res) {
                    //更新成功触发，审核是取消行为
                    Hook::listen('member_unverify', MemberBehaviorParam::create(['userid' => $val['userid']]));
                }
            }
            $this->success("取消审核成功！");
        } else {
            $this->redirect('Member/Member/index',
                ['search' => 1, '_filter[2]' => 'checked', '_operator[2]' => 'EQ', '_value[2]' => 0]);
        }
    }

    //用户授权管理
    public function connect() {
        $db = D("Member/Connect");
        if (IS_POST) {
            //批量删除
            $connectid = I('post.connectid');
            if ($db->connectDel($connectid)) {
                $this->success("操作成功！");
            } else {
                $this->error($db->getError());
            }
        } else {
            $connectid = I('get.connectid', 0, 'intval');
            if ($connectid) {
                //单个删除
                if ($db->connectDel($connectid)) {
                    $this->success("取消绑定成功！");
                } else {
                    $this->error($db->getError());
                }
            } else {
                $count = $db->count();
                $page = $this->page($count, 20, I('page', 1));
                $data = $db->limit($page->firstRow . ',' . $page->listRows)->order(array('connectid' => 'DESC'))->select();
                foreach ($data as $k => $r) {
                    $data[$k]['username'] = $this->member->where(array("userid" => $r['uid']))->getField("username");
                    $data[$k]['userid'] = $r['uid'];
                }
                $this->assign("Page", $page->show('Admin'));
                $this->assign("data", $data);
                $this->display();
            }
        }
    }

    //获取表单信息
    public function api_getForminfos() {
        $modelid = I('modelid', 0, 'intval');

        if (!$this->groupsModel[$modelid]) {
            $this->ajaxReturn(self::createReturn(false, null, '该模型不存在'));
        }

        $content_form = new \content_form($modelid);
        //字段内容
        $forminfos = $content_form->get();

        $this->ajaxReturn(self::createReturn(true, $forminfos, '获取表单信息成功'));
    }

    //添加附表信息
    protected function addMemberData($userid, $modelid, $info) {
        if ($modelid) {
            $tablename = getModel($modelid, 'tablename');
            $info['userid'] = $userid;
            M($tablename)->data($info)->add();
        }
    }
}
