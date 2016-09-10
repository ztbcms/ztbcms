<?php

/**
 * 采集处理类
 */
define("CHARSET", "utf-8");

class Collection {

    protected static $url, $config;
    //浏览器UA
    public static $userAgent = array(
        0 => "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.12 (KHTML, like Gecko) Maxthon/3.0 Chrome/26.0.1410.43 Safari/535.12",
        1 => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
        2 => "Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 6.0)",
        3 => "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",
        4 => "Mozilla/4.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)",
        5 => "Mozilla/5.0 (Windows NT 5.1; zh-CN; rv:1.9.1.3) Gecko/20100101 Firefox/19.0",
        6 => "Mozilla/5.0 (Windows NT 5.1; zh-CN) AppleWebKit/535.12 (KHTML, like Gecko) Chrome/26.0.1410.43 Safari/535.12",
        7 => "Mozilla/5.0 (Windows NT 5.1; zh-CN) AppleWebKit/535.12 (KHTML, like Gecko) Version/5.0.1 Safari/535.12",
        8 => "Opera/9.99 (Windows NT 5.1; U; zh-CN) Presto/9.9.9",
    );

    /**
     * 采集内容
     * @param string $url    采集地址
     * @param array $config  配置参数
     * @param integer $page  分页采集模式 1:全部列出模式 2:上下页模式
     */
    public static function get_content($url, $config, $page = 0) {
        set_time_limit(300);
        static $oldurl = array();
        $page = intval($page) ? intval($page) : 0;
        $html = self::get_html($url, $config);
        if ($html) {
            //把配置保存到全局变量中
            $GLOBALS['Collection_config'] = $config;
            if (empty($page)) {
                //获取标题
                if ($config['title_rule']) {
                    $title_rule = self::replace_sg($config['title_rule']);
                    $data['title'] = self::replace_item(self::cut_html($html, $title_rule[0], $title_rule[1]), $config['title_html_rule']);
                }

                //获取作者
                if ($config['author_rule']) {
                    $author_rule = self::replace_sg($config['author_rule']);
                    $data['author'] = self::replace_item(self::cut_html($html, $author_rule[0], $author_rule[1]), $config['author_html_rule']);
                }

                //获取来源
                if ($config['comeform_rule']) {
                    $comeform_rule = self::replace_sg($config['comeform_rule']);
                    $data['comeform'] = self::replace_item(self::cut_html($html, $comeform_rule[0], $comeform_rule[1]), $config['comeform_html_rule']);
                }

                //获取时间
                if ($config['time_rule']) {
                    $time_rule = self::replace_sg($config['time_rule']);
                    $data['time'] = strtotime(self::replace_item(self::cut_html($html, $time_rule[0], $time_rule[1]), $config['time_html_rule']));
                }

                //时间戳
                if (empty($data['time'])) {
                    $data['time'] = time();
                }

                //对自定义数据进行采集
                $config['customize_config'] = unserialize($config['customize_config']);
                if ($config['customize_config'] && is_array($config['customize_config'])) {
                    foreach ($config['customize_config'] as $k => $v) {
                        if (empty($v['rule'])) {
                            continue;
                        }
                        $rule = self::replace_sg($v['rule']);
                        $data[$v['en_name']] = self::replace_item(self::cut_html($html, $rule[0], $rule[1]), $v['html_rule']);
                    }
                }
            }

            //获取内容
            if ($config['content_rule']) {
                $content_rule = self::replace_sg($config['content_rule']);
                $data['content'] = self::replace_item(self::cut_html($html, $content_rule[0], $content_rule[1]), $config['content_html_rule']);
            }


            //处理分页
            if (in_array($page, array(0, 2)) && !empty($config['content_page_start']) && !empty($config['content_page_end'])) {
                $oldurl[] = $url;
                $tmp[] = $data['content'];
                $page_html = self::cut_html($html, $config['content_page_start'], $config['content_page_end']);
                //上下页模式
                if ($config['content_page_rule'] == 2 && in_array($page, array(0, 2)) && $page_html) {
                    preg_match_all('/<a[^>]*href=[\'"]?([^>\'" ]*)[\'"]?[^>]*>([^<\/]*)<\/a>/i', $page_html, $out);
                    if (!empty($out[1]) && !empty($out[2])) {
                        foreach ($out[2] as $k => $v) {
                            if (strpos($v, $config['content_nextpage']) === false)
                                continue;
                            if ($out[1][$k] == '#')
                                continue;
                            $out[1][$k] = self::url_check($out[1][$k], $url, $config);
                            if (in_array($out[1][$k], $oldurl))
                                continue;
                            $oldurl[] = $out[1][$k];
                            $results = self::get_content($out[1][$k], $config, 2);
                            if (!in_array($results['content'], $tmp))
                                $tmp[] = $results['content'];
                        }
                    }
                }

                //全部罗列模式
                if ($config['content_page_rule'] == 1 && $page == 0 && $page_html) {
                    preg_match_all('/<a[^>]*href=[\'"]?([^>\'" ]*)[\'"]?/i', $page_html, $out);
                    if (is_array($out[1]) && !empty($out[1])) {

                        $out = array_unique($out[1]);
                        foreach ($out as $k => $v) {
                            if ($out[1][$k] == '#')
                                continue;
                            $v = self::url_check($v, $url, $config);
                            $results = self::get_content($v, $config, 1);
                            if (!in_array($results['content'], $tmp))
                                $tmp[] = $results['content'];
                        }
                    }
                }
                $data['content'] = $config['content_page'] == 1 ? implode('[page]', $tmp) : implode('', $tmp);
            }

            if ($page == 0) {
                self::$url = $url;
                self::$config = $config;
                $data['content'] = stripslashes(preg_replace('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/ie', "self::download_img('$0', '$1')", $data['content']));

                //下载内容中的图片到本地
                if (empty($page) && !empty($data['content']) && $config['down_attachment'] == 1) {
                    //下载远程图片到本地
                    $Attachment = service("Attachment", array(
                        "module" => "Collection",
                        "catid" => 0,
                        "isadmin" => 1,
                    ));
                    $data['content'] = $Attachment->download($data['content'], $config['watermark'] ? $config['watermark'] : false);
                }
            }
            return $data;
        }
    }

    /**
     * 转换图片地址为绝对路径，为下载做准备。
     * @param array $out 图片地址
     */
    protected static function download_img($old, $out) {
        if (!empty($old) && !empty($out) && strpos($out, '://') === false) {
            return str_replace($out, self::url_check($out, self::$url, self::$config), $old);
            exit;
        } else {
            return $old;
        }
    }

    /**
     * 得到需要采集的网页列表页
     * @param array $config 配置参数
     * @param integer $num  返回数
     */
    public static function url_list(&$config, $num = '') {
        $url = array();
        switch ($config['sourcetype']) {
            case '1'://序列化
                $num = empty($num) ? $config['pagesize_end'] : $num;
                for ($i = $config['pagesize_start']; $i <= $num; $i = $i + $config['par_num']) {
                    $url[] = str_replace('(*)', $i, $config['urlpage']);
                }
                break;
            case '2'://多网址
                $url = explode("\r\n", $config['urlpage']);
                break;
            case '3'://单一网址
            case '4'://RSS
                $url[] = $config['urlpage'];
                break;
        }
        return $url;
    }

    /**
     * 获取文章网址
     * @param string $url           采集地址
     * @param array $config         配置
     */
    public static function get_url_lists($url, &$config) {
        //抓去内容
        $html = self::get_html($url, $config);
        if ($html) {
            //RSS
            if ($config['sourcetype'] == 4) {
                import('@.ORG.Xml');
                $xml = new Xml();
                $html = $xml->xml_unserialize($html);
                $data = array();
                if (is_array($html['rss']['channel']['item']))
                    foreach ($html['rss']['channel']['item'] as $k => $v) {
                        $data[$k]['url'] = $v['link'];
                        $data[$k]['title'] = $v['title'];
                    }
            } else {
                $html = self::cut_html($html, $config['url_start'], $config['url_end']);
                $html = str_replace(array("\r", "\n"), '', $html);
                $html = str_replace(array("</a>", "</A>"), "</a>\n", $html);
                //如果有使用精确网址获取方式，否则使用模糊方式
                if ($config['url_regular']) {
                    $Regular = str_replace(
                            array(".", "(", ")", "?", "*", "/", "-", "|", "+", "^", "{", "}", "[网址]", "[", "]", "\$"), array("\.", "\(", "\)", "\?", "(.*?)", "\/", "\-", "\|", "\+", "\^", "\{", "\}", "(.*?)", "\[", "\]", "\\$"), $config['url_regular']
                    );
                    preg_match_all('/' . $Regular . '/i', $html, $out);
                    $out[1] = array_unique($out[1]);
                    $out[2] = array_unique($out[2]);
                    $data = array();
                    foreach ($out[1] as $k => $r) {
                        if ($r) {
                            if ($config['url_contain']) {
                                if (strpos($r, $config['url_contain']) === false) {
                                    continue;
                                }
                            }

                            if ($config['url_except']) {
                                if (strpos($r, $config['url_except']) !== false) {
                                    continue;
                                }
                            }
                            $url2 = $r;
                            $url2 = self::url_check($url2, $url, $config);

                            $data[$k]['url'] = $url2;
                            $data[$k]['title'] = strip_tags($out[2][$k]);
                        } else {
                            continue;
                        }
                    }
                } else {
                    preg_match_all('/<a([^>]*)>([^\/a>].*)<\/a>/i', $html, $out);
                    $out[1] = array_unique($out[1]);
                    $out[2] = array_unique($out[2]);
                    $data = array();
                    foreach ($out[1] as $k => $v) {
                        if (preg_match('/href=[\'"]?([^\'" ]*)[\'"]?/i', $v, $match_out)) {
                            if ($config['url_contain']) {
                                if (strpos($match_out[1], $config['url_contain']) === false) {
                                    continue;
                                }
                            }

                            if ($config['url_except']) {
                                if (strpos($match_out[1], $config['url_except']) !== false) {
                                    continue;
                                }
                            }
                            $url2 = $match_out[1];
                            $url2 = self::url_check($url2, $url, $config);

                            $data[$k]['url'] = $url2;
                            $data[$k]['title'] = strip_tags($out[2][$k]);
                        } else {
                            continue;
                        }
                    }
                }
            }
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 获取远程HTML
     * @param string $url    获取地址
     * @param array $config  配置
     */
    protected static function get_html($url, &$config) {
        $Curl = new Curl();
        //检测是否支持
        if (false !== $Curl->create()) {
            $rand = rand(0, count(self::$userAgent));
            $userAgent = self::$userAgent[$rand];
            $ip = rand(25, 127) . '.' . rand(25, 127) . '.' . rand(25, 127) . '.' . rand(25, 127);
            $httpHeaders = array(
                'X-Forwarded-For:' . $ip . '',
                'CLIENT-IP:' . $ip . '',
            );
            $html = $Curl->get($url, $userAgent, $httpHeaders);
        } else {
            try {
                $html = file_get_contents($url);
            } catch (Exception $exc) {
                $html = false;
            }
        }
        if (!empty($url) && $html) {
            //检查编码是否一致，不是转码。。。
            if (CHARSET != $config['sourcecharset'] && $config['sourcetype'] != 4) {
                $html = iconv($config['sourcecharset'], CHARSET . '//IGNORE', $html);
            }
            return $html;
        } else {
            return false;
        }
    }

    /**
     * 
     * HTML切取
     * @param string $html    要进入切取的HTML代码
     * @param string $start   开始
     * @param string $end     结束
     */
    protected static function cut_html($html, $start, $end) {
        if (empty($html)) {
            return false;
        }
        $html = str_replace(array("\r", "\n"), "", $html);
        $start = str_replace(array("\r", "\n"), "", $start);
        $end = str_replace(array("\r", "\n"), "", $end);
        $html = explode(trim($start), $html);
        if (is_array($html)) {
            $html = explode(trim($end), $html[1]);
        }
        return $html[0];
    }

    /**
     * 过滤代码
     * @param string $html  HTML代码
     * @param array $config 过滤配置
     */
    protected static function replace_item($html, $config) {
        if (empty($config)) {
            return $html;
        }
        import('@.Funs.funs', '', '.php');
        $config = explode("\n", $config);
        $patterns = $replace = array();
        $p = 0;
        foreach ($config as $k => $v) {
            if (empty($v)) {
                continue;
            }
            //检查函数执行 fun=function
            if (substr($v, 0, 4) == 'fun=') {
                $args = explode("|", $v, 2);
                $fun = strtolower(trim(substr($args[0], 4)));
                try {
                    //是否带多个参数
                    if (isset($args[1])) {
                        if (strstr($args[1], '###')) {
                            $par = explode(",", $args[1]);
                            foreach ($par as $k => $v) {
                                if ($v === "###") {
                                    $par[$k] = $html;
                                }
                            }
                        } else {
                            $par = explode(",", $args[1]);
                        }
                        $html = call_user_func_array($fun, $par);
                    } else {
                        $html = call_user_func($fun, $html);
                    }
                } catch (Exception $exc) {
                    
                }
            } else {
                $c = explode('[|]', $v);
                $patterns = '/' . str_replace('/', '\/', $c[0]) . '/i';
                $replace = $c[1];
                $html = preg_replace($patterns, $replace, $html);
            }
        }
        return $html;
    }

    /**
     * 替换采集内容
     * @param $html 采集规则
     */
    protected static function replace_sg($html) {
        $list = explode("[内容]", $html);
        if (is_array($list)) {
            foreach ($list as $k => $v) {
                $list[$k] = str_replace(array("\r", "\n"), '', trim($v));
            }
        }
        return $list;
    }

    /**
     * URL地址检查
     * @param string $url      需要检查的URL
     * @param string $baseurl  基本URL
     * @param array $config    配置信息
     */
    protected static function url_check($url, $baseurl, $config) {
        $urlinfo = parse_url($baseurl);

        $baseurl = $urlinfo['scheme'] . '://' . $urlinfo['host'] . (substr($urlinfo['path'], -1, 1) === '/' ? substr($urlinfo['path'], 0, -1) : str_replace('\\', '/', dirname($urlinfo['path']))) . '/';
        if (strpos($url, '://') === false) {
            if ($url[0] == '/') {
                $url = $urlinfo['scheme'] . '://' . $urlinfo['host'] . $url;
            } else {
                if ($config['page_base']) {
                    $url = $config['page_base'] . $url;
                } else {
                    $url = $baseurl . $url;
                }
            }
        }
        return $url;
    }

}
