<?php
namespace Shop\Controller;

use Common\Controller\Base;
use Shop\Service\GoodsService;

class GoodsApiController extends Base {
    /**
     * @param $goods_id
     */
    public function goods_info($goods_id) {
        $goods = M('Goods')->where("goods_id = $goods_id")->find();
        if (empty($goods) || ($goods['is_on_sale'] == 0)) {
            $this->error('该商品已经下架', '', true);
        }
        $goods_images_list = M('GoodsImages')->where("goods_id = '%d'", $goods_id)->select(); // 商品 图册
        $goods_attribute = M('GoodsAttribute')->where("type_id='%d'",
            $goods['goods_type'])->getField('attr_id,attr_name'); // 查询属性
        $goods_attr_list = M('GoodsAttr')->where("goods_id = '%d'", $goods_id)->select(); // 查询商品属性表
        $spec_goods_price = M('spec_goods_price')->where("goods_id = '%d'",
            $goods_id)->getField("key,price,store_count"); // 规格 对应 价格 库存表
        $filter_spec = GoodsService::get_spec($goods_id);
        $data = [
            'goods_info' => $goods,
            'goods_images_list' => $goods_images_list, //商品图册
            'goods_attribute' => $goods_attribute, //商品所属的属性
            'goods_attr_list' => $goods_attr_list, //商品属性的值
            'spec_goods_price' => $spec_goods_price, //各个规格商品的价格
            'filter_spec' => $filter_spec //商品所属规格信息
        ];

        $this->success($data, '', true);
    }

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

