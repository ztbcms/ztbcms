<?php

// +----------------------------------------------------------------------
// | 商品类型管理
// +----------------------------------------------------------------------

namespace Shop\Controller;
use Common\Controller\AdminBase;
use Shop\Util\AjaxPage;

class CommentController extends AdminBase {
    public function index(){
        $this->display();
    }

    public function ajaxindex(){
        $model = M('ShopComment');
        $username = I('nickname','','trim');
        $content = I('content','','trim');
        $where=' parent_id = 0';
        if($username){
            $where .= " AND username='$username'";
        }
        if($content){
            $where .= " AND content like '%{$content}%'";
        }        
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,16);
        $show = $Page->show();
                
        $comment_list = $model->where($where)->order('add_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        if(!empty($comment_list))
        {
            $goods_id_arr = get_arr_column($comment_list, 'goods_id');
            $goods_list = M('Goods')->where("goods_id in (".  implode(',', $goods_id_arr).")")->getField("goods_id,goods_name");
        }
        $this->assign('goods_list',$goods_list);
        $this->assign('comment_list',$comment_list);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    } 
    
    public function detail(){
        $id = I('get.id');
        $res = M('ShopComment')->where(array('comment_id'=>$id))->find();
        if(!$res){
            exit($this->error('不存在该评论'));
        }
        if(IS_POST){
            $add['parent_id'] = $id;
            $add['content'] = I('post.content');
            $add['goods_id'] = $res['goods_id'];
            $add['add_time'] = time();
            $add['username'] = 'admin';

            $add['is_show'] = 1;

            $row =  M('ShopComment')->add($add);
            if($row){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
            exit;

        }
        $reply = M('ShopComment')->where(array('parent_id'=>$id))->select(); // 评论回复列表
         
        $this->assign('comment',$res);
        $this->assign('reply',$reply);
        $this->display();
    } 
    public function op(){
        $type = I('post.type');
        $selected_id = I('post.selected');
        if(!in_array($type,array('del','show','hide')) || !$selected_id)
            $this->error('非法操作');
        $where = "comment_id IN ({$selected_id})";
        if($type == 'del'){
            //删除回复
            $where .= " OR parent_id IN ({$selected_id})";
            $row = M('ShopComment')->where($where)->delete();
//            exit(M()->getLastSql());
        }
        if($type == 'show'){
            $row = M('ShopComment')->where($where)->save(array('is_show'=>1));
        }
        if($type == 'hide'){
            $row = M('ShopComment')->where($where)->save(array('is_show'=>0));
        }
        if(!$row)
            $this->error('操作失败');
        $this->success('操作成功');

    }
    public function del(){
        $id = I('get.id');
        $row = M('ShopComment')->where(array('comment_id'=>$id))->delete();
        if($row){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    public function ask_list(){
    	$this->display();
    }
      public function ajax_ask_list(){
    	$model = M('goods_consult');
    	$username = I('nickname','','trim');
    	$content = I('content','','trim');
    	$where=' parent_id = 0';
    	if($username){
    		$where .= " AND username='$username'";
    	}
    	if($content){
    		$where .= " AND content like '%{$content}%'";
    	}
        $count = $model->where($where)->count();        
        $Page  = new AjaxPage($count,16);
        $show = $Page->show();            	
    	
        $comment_list = $model->where($where)->order('add_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select(); 
    	if(!empty($comment_list))
    	{
    		$goods_id_arr = get_arr_column($comment_list, 'goods_id');
    		$goods_list = M('Goods')->where("goods_id in (".  implode(',', $goods_id_arr).")")->getField("goods_id,goods_name");
    	}
    	$consult_type = array(0=>'默认咨询',1=>'商品咨询',2=>'支付咨询',3=>'配送',4=>'售后');
    	$this->assign('consult_type',$consult_type);
    	$this->assign('goods_list',$goods_list);
    	$this->assign('comment_list',$comment_list);
    	$this->assign('page',$show);// 赋值分页输出
    	$this->display();
    }
     
    public function consult_info(){
    	$id = I('get.id');
    	$res = M('goods_consult')->where(array('id'=>$id))->find();
    	if(!$res){
    		exit($this->error('不存在该咨询'));
    	}
    	if(IS_POST){
    		$add['parent_id'] = $id;
    		$add['content'] = I('post.content');
    		$add['goods_id'] = $res['goods_id'];
                $add['consult_type'] = $res['consult_type'];
    		$add['add_time'] = time();    		
    		$add['is_show'] = 1;   	
    		$row =  M('goods_consult')->add($add);
    		if($row){
    			$this->success('添加成功');
    		}else{
    			$this->error('添加失败');
    		}
    		exit;    	
    	}
    	$reply = M('goods_consult')->where(array('parent_id'=>$id))->select(); // 咨询回复列表   	 
    	$this->assign('comment',$res);
    	$this->assign('reply',$reply);
    	$this->display();
    }
     public function ask_handle(){
    	$type = I('post.type');
    	$selected_id = I('post.selected');        
    	if(!in_array($type,array('del','show','hide')) || !$selected_id)
    		$this->error('操作完成');
    
        $selected_id = implode(',',$selected_id);
    	if($type == 'del'){
    		//删除咨询
    		$where .= "( id IN ({$selected_id}) OR parent_id IN ({$selected_id})) ";
    		$row = M('goods_consult')->where($where)->delete();
    	}
    	if($type == 'show'){
    		$row = M('goods_consult')->where("id IN ({$selected_id})")->save(array('is_show'=>1));
    	}
    	if($type == 'hide'){
    		$row = M('goods_consult')->where("id IN ({$selected_id})")->save(array('is_show'=>0));
    	}    		
    	$this->success('操作完成');
    }
}