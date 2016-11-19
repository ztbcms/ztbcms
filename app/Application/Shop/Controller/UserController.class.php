<?php
namespace Shop\Controller;
use Common\Controller\AdminBase;
class UserController extends AdminBase{
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
}