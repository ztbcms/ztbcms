<?php
namespace Shop\Service;

class GoodsService extends BaseService {

    /**
     * 获取指定的商品列表
     * @param     $where
     * @param     $catid
     * @param     $order
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function get_goods_list($where, $catid, $order, $page = 1, $limit = 20) {
        if ($catid) {
            $where['cat_id'] = ['in', getCatGrandson($catid)];
        }
        $goods_list = M('Goods')->where($where)->page($page, $limit)->order($order)->select();
        $total_count = M('Goods')->where($where)->count();
        $res = [
            'goods_list' => $goods_list ? $goods_list : [],
            'page' => $page,
            'limit' => $limit,
            'total_count' => $total_count,
        ];

        return $res;
    }
}

