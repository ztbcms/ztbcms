<?php
namespace Shop\Logic;

use Common\Model\RelationModel;
/**
 * 分类逻辑定义
 * Class CatsLogic
 * @package Home\Logic
 */
class GoodsLogic extends RelationModel
{

    /**
     * 获得指定分类下的子分类的数组     
     * @access  public
     * @param   int     $cat_id     分类的ID
     * @param   int     $selected   当前选中分类的ID
     * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
     * @param   int     $level      限定返回的级数。为0时返回所有级数
     * @return  mix
     */
    public function goods_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
    {
                global $goods_category, $goods_category2;                
                $sql = "SELECT * FROM  __PREFIX__goods_category ORDER BY parent_id , sort_order ASC";
                $goods_category = D('goods_category')->query($sql);
                $goods_category = convert_arr_key($goods_category, 'id');
                
                foreach ($goods_category AS $key => $value)
                {
                    if($value['level'] == 1)
                        $this->get_cat_tree($value['id']);                                
                }
                /*
                foreach ($goods_category2 AS $key => $value)
                {
                        $strpad_count = $value['level']*10;
                        echo str_pad('',$strpad_count,"-",STR_PAD_LEFT);             
                        echo $value['name'];
                        echo "<br/>";
                }*/
                return $goods_category2;               
    }
    
    /**
     * 获取指定id下的 所有分类      
     * @global type $goods_category 所有商品分类
     * @param type $id 当前显示的 菜单id
     * @return 返回数组 Description
     */
    public function get_cat_tree($id)
    {
        global $goods_category, $goods_category2;          
        $goods_category2[$id] = $goods_category[$id];    
        foreach ($goods_category AS $key => $value){
             if($value['parent_id'] == $id)
             {                 
                $this->get_cat_tree($value['id']);  
                $goods_category2[$id]['have_son'] = 1; // 还有下级
             }
        }               
    }
    
    
    /**
     * 移除指定$parent_id_path 分类以及下的所有分类
     * @global type $cat_list 所有商品分类
     * @param type $parent_id_path 指定的id
     * @return 返回数组 Description
     */
    public function remove_cat($cat_list,$parent_id_path)
    {
        foreach ($cat_list AS $key => $value){
             if(strstr($value['parent_id_path'],$parent_id_path))
             {                 
                 unset($cat_list[$value['id']]); 
             }
        }     
        return $cat_list;
    }
    
    /**
     * 改变或者添加分类时 需要修改他下面的 parent_id_path  和 level 
     * @global type $cat_list 所有商品分类
     * @param type $parent_id_path 指定的id
     * @return 返回数组 Description
     */
    public function refresh_cat($id)
    {            
        $GoodsCategory = M("GoodsCategory"); // 实例化User对象
        $cat = $GoodsCategory->where("id = $id")->find(); // 找出他自己
        // 刚新增的分类先把它的值重置一下
        if($cat['parent_id_path'] == '')
        {
            ($cat['parent_id'] == 0) && $GoodsCategory->execute("UPDATE __PREFIX__goods_category set  parent_id_path = '0_$id', level = 1 where id = $id"); // 如果是一级分类               
            $GoodsCategory->execute("UPDATE __PREFIX__goods_category AS a ,__PREFIX__goods_category AS b SET a.parent_id_path = CONCAT_WS('_',b.parent_id_path,'$id'),a.level = (b.level+1) WHERE a.parent_id=b.id AND a.id = $id");                
            $cat = $GoodsCategory->where("id = $id")->find(); // 从新找出他自己
        }        
        
        if($cat['parent_id'] == 0) //有可能是顶级分类 他没有老爸
        {
            $parent_cat['parent_id_path'] =   '0';   
            $parent_cat['level'] = 0;
        }
        else{
            $parent_cat = $GoodsCategory->where("id = {$cat['parent_id']}")->find(); // 找出他老爸的parent_id_path
        }        
        $replace_level = $cat['level'] - ($parent_cat['level'] + 1); // 看看他 相比原来的等级 升级了多少  ($parent_cat['level'] + 1) 他老爸等级加一 就是他现在要改的等级
        $replace_str = $parent_cat['parent_id_path'].'_'.$id;                
        $GoodsCategory->execute("UPDATE `__PREFIX__goods_category` SET parent_id_path = REPLACE(parent_id_path,'{$cat['parent_id_path']}','$replace_str'), level = (level - $replace_level) WHERE  parent_id_path LIKE '{$cat['parent_id_path']}%'");        
    }    

    /**
     * 动态获取商品属性输入框 根据不同的数据返回不同的输入框类型
     * @param int $goods_id 商品id
     * @param int $type_id 商品属性类型id
     */
    public function getAttrInput($goods_id,$type_id)
    {
        header("Content-type: text/html; charset=utf-8");
        $GoodsAttribute = D('GoodsAttribute');
        $attributeList = $GoodsAttribute->where("type_id = $type_id")->select();                                
        
        foreach($attributeList as $key => $val)
        {                                                                        
            
            $curAttrVal = $this->getGoodsAttrVal(NULL,$goods_id, $val['attr_id']);
             //促使他 循环
            if(count($curAttrVal) == 0)
                $curAttrVal[] = array('goods_attr_id' =>'','goods_id' => '','attr_id' => '','attr_value' => '','attr_price' => '');
            foreach($curAttrVal as $k =>$v)
            {                                        
                            $str .= "<tr class='attr_{$val['attr_id']}'>";            
                            $addDelAttr = ''; // 加减符号
                            // 单选属性 或者 复选属性
                            if($val['attr_type'] == 1 || $val['attr_type'] == 2)
                            {
                                if($k == 0)                                
                                    $addDelAttr .= "<a onclick='addAttr(this)' href='javascript:void(0);'>[+]</a>&nbsp&nbsp";                                                                    
                                else                                
                                     $addDelAttr .= "<a onclick='delAttr(this)' href='javascript:void(0);'>[-]</a>&nbsp&nbsp";                               
                            }

                            $str .= "<td>$addDelAttr {$val['attr_name']}</td> <td>";            

                           // if($v['goods_attr_id'] > 0) //tp_goods_attr 表id
                           //     $str .= "<input type='hidden' name='goods_attr_id[]' value='{$v['goods_attr_id']}'/>";
                                    
                            // 手工录入
                            if($val['attr_input_type'] == 0)
                            {
                                $str .= "<input type='text' size='40' value='{$v['attr_value']}' name='attr_{$val['attr_id']}[]' />";
                            }
                            // 从下面的列表中选择（一行代表一个可选值）
                            if($val['attr_input_type'] == 1)
                            {
                                $str .= "<select name='attr_{$val['attr_id']}[]'>";
                                $tmp_option_val = explode(PHP_EOL, $val['attr_values']);
                                foreach($tmp_option_val as $k2=>$v2)
                                {
                                    // 编辑的时候 有选中值
                                    $v2 = preg_replace("/\s/","",$v2);
                                    if($v['attr_value'] == $v2)
                                        $str .= "<option selected='selected' value='{$v2}'>{$v2}</option>";
                                    else
                                        $str .= "<option value='{$v2}'>{$v2}</option>";
                                }
                                $str .= "</select>";                
                                //$str .= "属性价格<input type='text' maxlength='10' size='5' value='{$v['attr_price']}' name='attr_price_{$val['attr_id']}[]'>";
                            }
                            // 多行文本框
                            if($val['attr_input_type'] == 2)
                            {
                                $str .= "<textarea cols='40' rows='3' name='attr_{$val['attr_id']}[]'>{$v['attr_value']}</textarea>";
                                //$str .= "属性价格<input type='text' maxlength='10' size='5' value='{$v['attr_price']}' name='attr_price_{$val['attr_id']}[]'>";
                            }                                                        
                            $str .= "</td></tr>";
                            //$str .= "<br/>";            
            }                        
            
        }        
        return  $str;
    }
    
    /**
     * 获取 tp_goods_attr 表中指定 goods_id  指定 attr_id  或者 指定 goods_attr_id 的值 可是字符串 可是数组
     * @param int $goods_attr_id tp_goods_attr表id
     * @param int $goods_id 商品id
     * @param int $attr_id 商品属性id
     * @return array 返回数组
     */
    public function getGoodsAttrVal($goods_attr_id = 0 ,$goods_id = 0, $attr_id = 0)
    {
        $GoodsAttr = D('GoodsAttr');        
        if($goods_attr_id > 0)
            return $GoodsAttr->where("goods_attr_id = $goods_attr_id")->select(); 
        if($goods_id > 0 && $attr_id > 0)
            return $GoodsAttr->where("goods_id = $goods_id and attr_id = $attr_id")->select();        
    }
   
    /**
     *  给指定商品添加属性 或修改属性 更新到 tp_goods_attr
     * @param int $goods_id  商品id
     * @param int $goods_type  商品类型id
     */
    public function saveGoodsAttr($goods_id,$goods_type)
    {  
        $GoodsAttr = D('GoodsAttr');
        $Goods = M("Goods");
                
         // 属性类型被更改了 就先删除以前的属性类型 或者没有属性 则删除        
        if($goods_type == 0)  
        {
            $GoodsAttr->where('goods_id = '.$goods_id)->delete(); 
            return;
        }
        
            $GoodsAttrList = $GoodsAttr->where('goods_id = '.$goods_id)->select();
            
            $old_goods_attr = array(); // 数据库中的的属性  以 attr_id _ 和值的 组合为键名
            foreach($GoodsAttrList as $k => $v)
            {                
                $old_goods_attr[$v['attr_id'].'_'.$v['attr_value']] = $v;
            }            
                              
            // post 提交的属性  以 attr_id _ 和值的 组合为键名    
            $post_goods_attr = array();
            foreach($_POST as $k => $v)
            {
                $attr_id = str_replace('attr_','',$k);
                if(!strstr($k, 'attr_') || strstr($k, 'attr_price_'))
                   continue;                                 
               foreach ($v as $k2 => $v2)
               {                      
                   $v2 = str_replace('_', '', $v2); // 替换特殊字符
                   $v2 = str_replace('@', '', $v2); // 替换特殊字符
                   $v2 = trim($v2);
                   
                   if(empty($v2))
                       continue;
                   
                   
                   $tmp_key = $attr_id."_".$v2;
                   $attr_price = $_POST["attr_price_$attr_id"][$k2]; 
                   $attr_price = $attr_price ? $attr_price : 0;
                   if(array_key_exists($tmp_key , $old_goods_attr)) // 如果这个属性 原来就存在
                   {   
                       if($old_goods_attr[$tmp_key]['attr_price'] != $attr_price) // 并且价格不一样 就做更新处理
                       {                       
                            $goods_attr_id = $old_goods_attr[$tmp_key]['goods_attr_id'];                         
                            $GoodsAttr->where("goods_attr_id = $goods_attr_id")->save(array('attr_price'=>$attr_price));                       
                       }
                   }
                   else // 否则这个属性 数据库中不存在 说明要做删除操作
                   {
                       $GoodsAttr->add(array('goods_id'=>$goods_id,'attr_id'=>$attr_id,'attr_value'=>$v2,'attr_price'=>$attr_price));                       
                   }
                   unset($old_goods_attr[$tmp_key]);
               }
                
            }     
            //file_put_contents("b.html", print_r($post_goods_attr,true));
            // 没有被 unset($old_goods_attr[$tmp_key]); 掉是 说明 数据库中存在 表单中没有提交过来则要删除操作
            foreach($old_goods_attr as $k => $v)
            {                
               $GoodsAttr->where('goods_attr_id = '.$v['goods_attr_id'])->delete(); // 
            }                       

    }
    
    /**
     * 获取 tp_spec_item表 指定规格id的 规格项
     * @param int $spec_id 规格id
     * @return array 返回数组
     */
    public function getSpecItem($spec_id)
    { 
        $model = M('SpecItem');        
        $arr = $model->where("spec_id = $spec_id")->order('id')->select(); 
        $arr = get_id_val($arr, 'id','item');        
        return $arr;
    }    
    
    /**
     * 获取 规格的 笛卡尔积
     * @param $goods_id 商品 id     
     * @param $spec_arr 笛卡尔积
     * @return string 返回表格字符串
     */
    public function getSpecInput($goods_id, $spec_arr)
    {
        // <input name="item[2_4_7][price]" value="100" /><input name="item[2_4_7][name]" value="蓝色_S_长袖" />        
        /*$spec_arr = array(         
            20 => array('7','8','9'),
            10=>array('1','2'),
            1 => array('3','4'),
            
        );  */        
        // 排序
        foreach ($spec_arr as $k => $v)
        {
            $spec_arr_sort[$k] = count($v);
        }
        asort($spec_arr_sort);        
        foreach ($spec_arr_sort as $key =>$val)
        {
            $spec_arr2[$key] = $spec_arr[$key];
        }
     
        
         $clo_name = array_keys($spec_arr2);         
         $spec_arr2 = combineDika($spec_arr2); //  获取 规格的 笛卡尔积                 
                       
         $spec = M('Spec')->getField('id,name'); // 规格表
         $specItem = M('SpecItem')->getField('id,item,spec_id');//规格项
         $keySpecGoodsPrice = M('SpecGoodsPrice')->where('goods_id = '.$goods_id)->getField('key,key_name,price,store_count,bar_code,sku');//规格项
                          
       $str = "<table class='table table-bordered' id='spec_input_tab'>";
       $str .="<tr>";       
       // 显示第一行的数据
       foreach ($clo_name as $k => $v) 
       {
           $str .=" <td><b>{$spec[$v]}</b></td>";
       }    
        $str .="<td><b>价格</b></td>
               <td><b>库存</b></td>
               <td><b>SKU</b></td>
             </tr>";
       // 显示第二行开始 
       foreach ($spec_arr2 as $k => $v) 
       {
            $str .="<tr>";
            $item_key_name = array();
            foreach($v as $k2 => $v2)
            {
                $str .="<td>{$specItem[$v2][item]}</td>";
                $item_key_name[$v2] = $spec[$specItem[$v2]['spec_id']].':'.$specItem[$v2]['item'];
            }   
            ksort($item_key_name);            
            $item_key = implode('_', array_keys($item_key_name));
            $item_name = implode(' ', $item_key_name);
            
			$keySpecGoodsPrice[$item_key][price] ? false : $keySpecGoodsPrice[$item_key][price] = 0; // 价格默认为0
			$keySpecGoodsPrice[$item_key][store_count] ? false : $keySpecGoodsPrice[$item_key][store_count] = 0; //库存默认为0
            $str .="<td><input name='item[$item_key][price]' value='{$keySpecGoodsPrice[$item_key][price]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' /></td>";
            $str .="<td><input name='item[$item_key][store_count]' value='{$keySpecGoodsPrice[$item_key][store_count]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";            
            $str .="<td><input name='item[$item_key][sku]' value='{$keySpecGoodsPrice[$item_key][sku]}' />
                <input type='hidden' name='item[$item_key][key_name]' value='$item_name' /></td>";
            $str .="</tr>";           
       }
        $str .= "</table>";
       return $str;   
    }
    
    /**
     * 获取指定规格类型下面的所有规格  但不包括规格项 供商品分类列表页帅选作用
     * @param type $type_id
     * @param type $checked
     */
    function GetSpecCheckboxList($type_id, $checked = array()){
        $list = M('Spec')->where("type_id = $type_id")->order('`order` desc')->select();        
        //$list = M('Spec')->where("1=1")->order('`order` desc')->select();        
        $str = '';
        
        foreach($list as $key => $val)
        {
            if(in_array($val['id'],$checked))
                $str .= $val['name'].":<input type='checkbox' name='spec_id[]' value='{$val['id']}' checked='checked'/>&nbsp;&nbsp";
            else
                $str .= $val['name'].":<input type='checkbox' name='spec_id[]' value='{$val['id']}' />&nbsp;&nbsp";
        }
        return $str;
    }
    
    /**
     * 获取指定商品类型下面的所有属性  供商品分类列表页帅选作用
     * @param type $type_id
     * @param type $checked
     */
    function GetAttrCheckboxList($type_id, $checked = array()){                
        $list = M('GoodsAttribute')->where("type_id = $type_id and attr_index > 0 ")->order('`order` desc')->select();        
        //$list = M('Spec')->where("1=1")->order('`order` desc')->select();        
        $str = '';
        
        foreach($list as $key => $val)
        {
            if(in_array($val['attr_id'],$checked))
                $str .= $val['attr_name'].":<input type='checkbox' name='attr_id[]' value='{$val['attr_id']}' checked='checked'/>&nbsp;&nbsp";
            else
                $str .= $val['attr_name'].":<input type='checkbox' name='attr_id[]' value='{$val['attr_id']}' />&nbsp;&nbsp";
        }
        return $str;
    }
    
    /**
     *  获取选中的下拉框
     * @param type $cat_id
     */
    function find_parent_cat($cat_id)
    {
        if($cat_id == null) 
            return array();
        
        $cat_list =  M('goods_category')->getField('id,parent_id,level');
        $cat_level_arr[$cat_list[$cat_id]['level']] = $cat_id;

        // 找出他老爸
        $parent_id = $cat_list[$cat_id]['parent_id'];
        if($parent_id > 0)
             $cat_level_arr[$cat_list[$parent_id]['level']] = $parent_id;
        // 找出他爷爷
        $grandpa_id = $cat_list[$parent_id]['parent_id'];
        if($grandpa_id > 0)
             $cat_level_arr[$cat_list[$grandpa_id]['level']] = $grandpa_id;
        
        // 建议最多分 3级, 不要继续往下分太多级
        // 找出他祖父
        $grandfather_id = $cat_list[$grandpa_id]['parent_id'];
        if($grandfather_id > 0)
             $cat_level_arr[$cat_list[$grandfather_id]['level']] = $grandfather_id;
        
        return $cat_level_arr;      
    }        
    
    /**
     *  获取排好序的品牌列表    
     */
    function getSortBrands()
    {
        $brandList =  M("Brand")->select();
        $brandIdArr =  M("Brand")->where("name in (select `name` from `".C('DB_PREFIX')."brand` group by name having COUNT(id) > 1)")->getField('id,cat_id'); 
        $goodsCategoryArr = M('goodsCategory')->where("level = 1")->getField('id,name');
        $nameList = array();
        foreach($brandList as $k => $v)
        {
            
            $name = getFirstCharter($v['name']) .'  --   '. $v['name']; // 前面加上拼音首字母            
            
            if(array_key_exists($v[id],$brandIdArr) && $v[cat_id]) // 如果有双重品牌的 则加上分类名称            
                    $name .= ' ( '. $goodsCategoryArr[$v[cat_id]] . ' ) ';            
                
             $nameList[] = $v['name'] = $name; 
             $brandList[$k] = $v;
        }         
        array_multisort($nameList,SORT_STRING,SORT_ASC,$brandList); 

        return $brandList;
    }    
    
    /**
     *  获取排好序的分类列表     
     */
    function getSortCategory()
    {
        $categoryList =  M("GoodsCategory")->getField('id,name,parent_id,level');
        $nameList = array();
        foreach($categoryList as $k => $v)
        {
            
            //$str_pad = str_pad('',($v[level] * 5),'-',STR_PAD_LEFT);
            $name = getFirstCharter($v['name']) .' '. $v['name']; // 前面加上拼音首字母            
            //$name = getFirstCharter($v['name']) .' '. $v['name'].' '.$v['level']; // 前面加上拼音首字母            
            /*
            // 找他老爸
            $parent_id = $v['parent_id']; 
            if($parent_id)
                $name .= '--'.$categoryList[$parent_id]['name'];
            // 找他 爷爷
            $parent_id = $categoryList[$v['parent_id']]['parent_id'];
            if($parent_id)
                $name .= '--'.$categoryList[$parent_id]['name'];            
            */
             $nameList[] = $v['name'] = $name; 
             $categoryList[$k] = $v;
        }         
       array_multisort($nameList,SORT_STRING,SORT_ASC,$categoryList); 

        return $categoryList;
    }                    
}