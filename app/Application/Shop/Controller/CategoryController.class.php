<?php

// +----------------------------------------------------------------------
// | 商品分类管理
// +----------------------------------------------------------------------

namespace Shop\Controller;
use Common\Controller\AdminBase;
use Shop\Logic\GoodsLogic;

class CategoryController extends AdminBase {
    /**
     * 商品分类展示
     */
    public function index() {
        $GoodsLogic = new GoodsLogic();
        $cat_list = $GoodsLogic->goods_cat_list();
        $this->assign('cat_list', $cat_list);
        $this->display();
    }
    /**
     * 添加修改商品分类
     * 手动拷贝分类正则 ([\u4e00-\u9fa5/\w]+)  ('393','$1'),
     * select * from tp_goods_category where id = 393
     * select * from tp_goods_category where parent_id = 393
     * update tp_goods_category  set parent_id_path = concat_ws('_','0_76_393',id),`level` = 3 where parent_id = 393
     * insert into `tp_goods_category` (`parent_id`,`name`) values
     * ('393','时尚饰品'),
     */
    public function addEditCategory() {

        $GoodsLogic = new GoodsLogic();
        if (IS_GET) {
            $goods_category_info = D('GoodsCategory')->where('id=' . I('GET.id', 0))->find();
            $level_cat = $GoodsLogic->find_parent_cat($goods_category_info['id']); // 获取分类默认选中的下拉框

            $cat_list = M('goods_category')->where("parent_id = 0")->select(); // 已经改成联动菜单
            $this->assign('level_cat', $level_cat);
            $this->assign('cat_list', $cat_list);
            $this->assign('goods_category_info', $goods_category_info);
            $this->display('_category');
            exit;
        }

        $GoodsCategory = D('GoodsCategory'); //

        $type = $_POST['id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新
        //ajax提交验证
        if ($_GET['is_ajax'] == 1) {
            C('TOKEN_ON', false);

            if (!$GoodsCategory->create(NULL, $type)) // 根据表单提交的POST数据创建数据对象
            {
                //  编辑
                $return_arr = array(
                    'status' => -1,
                    'msg' => '操作失败!',
                    'data' => $GoodsCategory->getError(),
                );
                $this->ajaxReturn($return_arr);
            } else {
                //  form表单提交
                C('TOKEN_ON', true);

                $GoodsCategory->parent_id = $_POST['parent_id_1'];
                $_POST['parent_id_2'] && ($GoodsCategory->parent_id = $_POST['parent_id_2']);

                if ($GoodsCategory->id > 0 && $GoodsCategory->parent_id == $GoodsCategory->id) {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg' => '上级分类不能为自己',
                        'data' => '',
                    );
                    $this->ajaxReturn($return_arr);
                }
                if ($GoodsCategory->commission_rate > 100) {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg' => '分佣比例不得超过100%',
                        'data' => '',
                    );
                    $this->ajaxReturn($return_arr);
                }
                if ($type == 2) {
                    $GoodsCategory->save(); // 写入数据到数据库
                    $GoodsLogic->refresh_cat($_POST['id']);
                } else {
                    $insert_id = $GoodsCategory->add(); // 写入数据到数据库
                    $GoodsLogic->refresh_cat($insert_id);
                }
                $return_arr = array(
                    'status' => 1,
                    'msg' => '操作成功',
                    'data' => array('url' => U('Category/index')),
                );
                $this->ajaxReturn($return_arr);

            }
        }
    }
    /**
     * 删除分类
     */
    public function delGoodsCategory() {
        // 判断子分类
        $GoodsCategory = M("GoodsCategory");
        $count = $GoodsCategory->where("parent_id = {$_GET['id']}")->count("id");
        $count > 0 && $this->error('该分类下还有分类不得删除!', U('Shop/Category/index'));
        // 判断是否存在商品
        $goods_count = M('Goods')->where("cat_id = {$_GET['id']}")->count('1');
        $goods_count > 0 && $this->error('该分类下有商品不得删除!', U('Shop/Category/index'));
        // 删除分类
        $GoodsCategory->where("id = {$_GET['id']}")->delete();
        $this->success("操作成功!!!", U('Shop/Category/index'));
    }

}
