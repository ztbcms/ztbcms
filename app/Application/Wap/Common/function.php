<?php

/**
 * 获取wap访问的内容信息地址
 * @param type $data
 * @return string
 */
function geturl($data) {
    if (!is_array($data)) {
        return '';
    }
    return U('Wap/Index/shows', array('catid' => $data['catid'], 'id' => $data['id']));
}

/**
 * 获取栏目地址
 * @param type $catid 栏目id
 * @return string
 */
function caturl($catid) {
    if (empty($catid)) {
        return '';
    }
	$category = getCategory($catid);
    if ($category['modelid'] ==9999) {
        return  $category['url'];
 
    } else{
         return U('Wap/Index/lists', array('catid' => $catid)); 
    }
}