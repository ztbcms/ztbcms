<?php
namespace Shop\Controller;

use Common\Controller\Base;
use Shop\Service\GoodsService;

class GoodsApiController extends Base {
    /**
     *  获取商品列表
     */
    public function goods_list() {
        $where = [];
        if (I('get.is_recommend')) {
            $where['is_recommend'] = true;
        }
        if (I('get.is_new')) {
            $where['is_new'] = true;
        }
        if (I('get.is_hot')) {
            $where['is_hot'] = true;
        }
        $page = I('get.page', 1);
        $limit = I('get.limit', 20);

        $catid = I('get.catid', 0);
        $order = I('get.order', '');
        $goods_service = new GoodsService();
        $goods_res = $goods_service->get_goods_list($where, $catid, $order, $page, $limit);
        $this->success($goods_res, '', true);
    }
}

