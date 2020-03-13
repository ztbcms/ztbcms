<?php

// +----------------------------------------------------------------------
// | 会员组管理
// +----------------------------------------------------------------------

namespace Member\Controller;

use Common\Controller\AdminBase;

class GroupController extends AdminBase {

	//会员用户组模型
	protected $memberGroupModel = NULL;
	//会员数据模型
	protected $member = NULL;

	function _initialize() {
		parent::_initialize();
		import('Form');
		$this->memberGroupModel = D('Member_group');
	}

	//会员组管理
	public function index() {
		$this->member = D('Member');
		$data = $this->memberGroupModel->order(array("sort" => "ASC", "groupid" => "DESC"))->select();
		foreach ($data as $k => $v) {
			//统计会员总数
			$data[$k]['_count'] = $this->member->where(array("groupid" => $v['groupid']))->count('userid');
		}
		$this->assign("data", $data);
		$this->display('indexNew');
	}
	//测试完删除
//    public function indexNew(){
//        $this->display('indexNew');
//    }
	public function indexNewApi(){
        $this->member = D('Member');
        $data = $this->memberGroupModel->order(array("sort" => "ASC", "groupid" => "DESC"))->select();
        foreach ($data as $k => $v) {
            //统计会员总数
            $data[$k]['_count'] = $this->member->where(array("groupid" => $v['groupid']))->count('userid');
        }
        return $this->ajaxReturn(self::createReturn(true,$data));
    }

	//添加会员组
	public function add() {
		if (IS_POST) {
			$post = $_POST;
			$data = $this->memberGroupModel->create($post);
			if ($data) {
				if ($this->memberGroupModel->groupAdd($data)) {
					$this->success("添加成功！", U("Group/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->memberGroupModel->getError());
			}
		} else {
			$this->display();
		}
	}

    //添加会员组
    public function addApi() {
        if (IS_POST) {
            $post = $_POST;
            $newData = [];
            foreach($post['quanxian'] as $post_k => $post_v){
                $newData[$post_k] = $post_v ? 1 : 0;
            }
            foreach($post['checkList'] as $checkList_k => $checkList_v ){
                $newData[$checkList_v] = 1;
            }
            unset($post['quanxian']);
            unset($post['checkList']);
            $newData = array_merge()
            dump($post);


        }
    }

	//新增会员组页面
	public function addNew(){
        $this->display();
    }

	//编辑会员组
	public function edit() {
		if (IS_POST) {
			$post = $_POST;
			$data = $this->memberGroupModel->create($post);
			if ($data) {
				if ($this->memberGroupModel->groupEdit($data)) {
					$this->success("修改成功！", U("Group/index"));
				} else {
					$this->error("修改失败！");
				}
			} else {
				$this->error($this->memberGroupModel->getError());
			}
		} else {
			$groupid = I("get.groupid", 0, 'intval');
			$data = $this->memberGroupModel->where(array("groupid" => $groupid))->find();
			if (empty($data)) {
				$this->error("该会员组不存在！", U("Group/index"));
			}
			$this->assign("data", $data);
			$this->assign('expand', unserialize($data['expand']));
			$this->display();
		}
	}

	//删除会员组
	public function delete() {
		if (IS_POST) {
			$groupid = I('post.groupid');
			if (empty($groupid)) {
				$this->error("没有指定需要删除的会员组别！");
			}
			if ($this->memberGroupModel->groupDelete($groupid)) {
				//更新缓存
				$this->memberGroupModel->Membergroup_cache();
				$this->success("删除成功！", U("Group/index"));
			} else {
				$this->error($this->memberGroupModel->getError());
			}
		}
	}

	//排序
	public function sort() {
		if (IS_POST) {
			$sort = I('post.sort');
			if (is_array($sort)) {
				foreach ($sort as $gid => $pxid) {
					$this->memberGroupModel->where(array("groupid" => $gid))->save(array("sort" => $pxid));
				}
			}
			$this->success("排序更新成功！", U("Group/index"));
		} else {
			$this->error("请求方式错误！");
		}
	}

}
