<?php

// +----------------------------------------------------------------------
// | 插件相关函数 
// +----------------------------------------------------------------------

/**
 * 插件模板定位
 * @staticvar array $TemplateFileCache
 * @param type $templateFile
 * @param type $addonPath 插件目录
 * @return type
 */
function parseAddonTemplateFile($templateFile = '', $addonPath) {
    static $TemplateFileCache = array();
    C('TEMPLATE_NAME', $addonPath . 'View/');
    //模板标识
    if ('' == $templateFile) {
        $templateFile = C('TEMPLATE_NAME') . ucwords(ADDON_MODULE_NAME) . '/' . ACTION_NAME . C('TMPL_TEMPLATE_SUFFIX');
    }
    $key = md5($templateFile);
    if (isset($TemplateFileCache[$key])) {
        return $TemplateFileCache[$key];
    }
    if (false === strpos($templateFile, C('TMPL_TEMPLATE_SUFFIX'))) {
        // 解析规则为 模板主题:模块:操作 不支持 跨项目和跨分组调用
        $path = explode(':', $templateFile);
        $action = array_pop($path);
        $module = !empty($path) ? array_pop($path) : ucwords(ADDON_MODULE_NAME);
        $path = C("TEMPLATE_NAME");
        $depr = defined('GROUP_NAME') ? C('TMPL_FILE_DEPR') : '/';
        $templateFile = $path . $module . $depr . $action . C('TMPL_TEMPLATE_SUFFIX');
    }
    //区分大小写的文件判断，如果不存在，尝试一次使用默认主题
    if (!file_exists_case($templateFile)) {
        //记录日志
        $log = '模板:[' . $templateFile . ']不存在！';
        throw_exception($log);
    }
    $TemplateFileCache[$key] = $templateFile;
    return $templateFile;
}
