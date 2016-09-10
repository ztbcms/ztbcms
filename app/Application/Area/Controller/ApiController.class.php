<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Area\Controller;


use Area\Model\CityModel;
use Area\Model\DistrictModel;
use Area\Model\ProviceModel;
use Area\Model\StreetModel;
use Common\Controller\Base;


/**
 * 区域模块对外API
 *
 * @package Area\Controller
 */
class ApiController extends Base {


    protected function _initialize() {
        parent::_initialize();

    }

    /**
     * 省份
     */
    public function getProvinces() {
        $ProvinceModel = new ProviceModel();

        $this->ajaxReturn(array(
            'status' => 'success',
            'data' => $ProvinceModel->getProvinces()
        ));
    }

    /**
     * 市
     *
     * @param $id
     */
    public function getCitiesByProvinceId($id) {
        $CityModel = new CityModel();

        $this->ajaxReturn(array(
            'status' => 'success',
            'data' => $CityModel->getCitiesByProvinceId($id)
        ));
    }

    /**
     * 区、县
     *
     * @param $id
     */
    public function getDistrictsByCityId($id) {
        $DistrictModel = new DistrictModel();

        $this->ajaxReturn(array(
            'status' => 'success',
            'data' => $DistrictModel->getDistrictsByCityId($id)
        ));

    }

    /**
     * 街道
     *
     * @param $id
     */
    public function getStreetsByDistrictId($id) {
        $StreetModel = new StreetModel();

        $this->ajaxReturn(array(
            'status' => 'success',
            'data' => $StreetModel->getStreetsByDistrictId($id)
        ));
    }

}