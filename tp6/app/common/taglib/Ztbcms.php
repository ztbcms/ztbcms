<?php
/**
 * User: zhlhuang
 */

namespace app\common\taglib;

use think\Template;
use think\template\TagLib;
use app\common\taglib\Template as MyTemplate;

class Ztbcms extends TagLib {

    protected $includeFile = [];

    private $template;

    public function __construct(Template $template) {
        parent::__construct($template);
        //这里引用是自定义的template
        $this->template = new MyTemplate(config('view'));
    }

    /**
     * 定义标签列表
     */
    protected $tags = [
//        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'include' => ['attr' => 'file', 'close' => 0],
    ];

    public function tagInclude($tag): string {
        $file = $tag['file'] ?? ''; // name是必填项，这里不做判断了

        unset($tag['file']);
        $parseStr = $this->template->parseTemplateName($file);
        foreach ($tag as $k => $v) {
            // 以$开头字符串转换成模板变量
            if (0 === strpos($v, '$')) {
                $v = $this->template->get(substr($v, 1));
            }

            $parseStr = str_replace('[' . $k . ']', $v, $parseStr);
        }
        $this->template->parseInclude($parseStr);

        return $parseStr;
    }
}