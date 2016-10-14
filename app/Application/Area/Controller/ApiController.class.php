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
use Area\Model\SchoolModel;
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
     * 获取学校列表
     */

    public function getSchoolList($keyword = '', $page = 1, $limit = 10) {
        $schoolModel = new SchoolModel();
        if ($keyword) {
            $where['school_name'] = array('like', "%" . $keyword . "%");
        }
        $schools = $schoolModel->where($where)->page($page, $limit)->select();
        $this->ajaxReturn(array(
            'status' => 'success',
            'data' => $schools ? $schools : array(),
        ));
    }

    /**
     * 通过省份id获取学校列表
     */

    public function getSchoolListByProvinceId($p_id = 0, $page = 1, $limit = 20) {
        $schoolModel = new SchoolModel();
        $p_id ? $where['province_id'] = $p_id : null;
        $schools = $schoolModel->where($where)->page($page, $limit)->select();
        $this->ajaxReturn(array(
            'status' => 'success',
            'data' => $schools ? $schools : array(),
        ));
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
     * 根据省份名称模糊搜索城市列表
     * @param $provice_name
     */
    public function getCitiesByProvince($provice_name='') {
        $ProvinceModel = new ProviceModel();
        $where['areaname']=array('like',$provice_name."%");
        $provice=$ProvinceModel->where($where)->find();

        $id=$provice['id'];
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
     * 根据城市名称迷糊搜索区/县
     *
     * @param $city_name
     */
    public function getDistrictsByCity($city_name) {
        $CityModel = new CityModel();
        $where['areaname']=array('like',$city_name."%");
        $city=$CityModel->where($where)->find();
        $id=$city['id'];
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