<?php

namespace Shop\Controller;
use Common\Controller\AdminBase;

use Shop\Util\File;
use Shop\Util\Upload;
use Think\Log;

class UeditorController extends AdminBase{
    private $sub_name = array('date', 'Y/m-d');
    private $savePath = 'temp/';

    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set("Asia/Shanghai");
        
        $this->savePath = I('GET.savepath','temp').'/';
        
        error_reporting(E_ERROR | E_WARNING);
    }
    
    public function getContent()
    {
        echo '<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
            <script src="' . __ROOT__ . '/Public/plugins/Ueditor/ueditor.parse.js" type="text/javascript"></script>
            <script>' . " uParse('.content',{
                  'highlightJsUrl':'" . __ROOT__ . "/Public/plugins/Ueditor/third-party/SyntaxHighlighter/shCore.js',
                  'highlightCssUrl':" . __ROOT__ . "/Public/plugins/Ueditor/third-party/SyntaxHighlighter/shCoreDefault.css'
              })</script>";
        $content = htmlspecialchars(stripslashes($_REQUEST ['myEditor']));
        echo "<div class='content'>" . htmlspecialchars_decode($content) . "</div>";
    }

    /**
     *上传文件
     */
    public function fileUp()
    {
        $config = array(
            "savePath" => 'File/',
            "maxSize" =>  20000000, // 单位B
            "exts" => explode(",",  'zip,rar,doc,docx,zip,pdf,txt,ppt,pptx,xls,xlsx'),
            "subName" => $this->sub_name,
        );

        $upload = new Upload($config);
        $info = $upload->upload();

        if ($info) {
            $state = "SUCCESS";
        } else {
            $state = "ERROR" . $upload->getError();
        }

        $return_data['url'] = $info['upfile']['urlpath'];
        $return_data['fileType'] = $info['upfile']['ext'];
        $return_data['original'] = $info['upfile']['name'];
        $return_data['state'] = $state;
        $this->ajaxReturn($return_data,'JSON');
    }

    /**
     * 获取远程图片
     */
    public function getRemoteImage()
    {
        header("Content-Type: text/html; charset=utf-8");
        //远程抓取图片配置
        $config = array(
            "savePath" => UPLOAD_PATH . 'remote/' . date('Y') . '/' . date('m') . '/', //保存路径
            "allowFiles" => array(".gif", ".png", ".jpg", ".jpeg", ".bmp"), //文件允许格式
            "maxSize" => 20000000,
        );
        $uri = htmlspecialchars($_REQUEST['upfile']);
        $uri = str_replace("&amp;", "&", $uri);
        $this->getRemoteImage2($uri, $config);

    }

    /**
     * 远程抓取
     * @param $uri
     * @param $config
     */
    public function getRemoteImage2($uri, $config)
    {
        //忽略抓取时间限制
        set_time_limit(0);
        //ue_separate_ue  ue用于传递数据分割符号
        $imgUrls = explode("ue_separate_ue", $uri);
        $tmpNames = array();
        foreach ($imgUrls as $imgUrl) {
            //http开头验证
            if (strpos($imgUrl, "http") !== 0) {
                array_push($tmpNames, "https error");
                continue;
            }
            //sae环境 不兼容
            if (!defined('SAE_TMP_PATH')) {
                //获取请求头
                $heads = get_headers($imgUrl);
                //死链检测
                if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
                    array_push($tmpNames, "get_headers error");
                    continue;
                }
            }
            
            //格式验证(扩展名验证和Content-Type验证)
            $fileType = strtolower(strrchr($imgUrl, '.'));
            if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
                array_push($tmpNames, "Content-Type error");
                continue;
            }

            //打开输出缓冲区并获取远程图片
            ob_start();
            $context = stream_context_create(
                array(
                    'http' => array(
                        'follow_location' => false // don't follow redirects
                    )
                )
            );
            //请确保php.ini中的fopen wrappers已经激活
            readfile($imgUrl, false, $context);
            $img = ob_get_contents();
            ob_end_clean();

            //大小验证
            $uriSize = strlen($img); //得到图片大小
            $allowSize = 1024 * $config['maxSize'];
            if ($uriSize > $allowSize) {
                array_push($tmpNames, "maxSize error");
                continue;
            }

            $savePath = $config['savePath'];

            if (!defined('SAE_TMP_PATH')) {
                //非SAE
                //创建保存位置
                if (!file_exists($savePath)) {
                    mkdir($savePath, 0777, true);
                }
                //写入文件
                $tmpName = $savePath . rand(1, 10000) . time() . strrchr($imgUrl, '.');
                try {
                    File::writeFile($tmpName, $img, "a");

                    array_push($tmpNames, __ROOT__ . '/' . $tmpName);
                } catch (\Exception $e) {
                    array_push($tmpNames, "error");
                }
            } else {
                //SAE
                $Storage = new \SaeStorage();
                $domain = C('SaeStorage');
                $destFileName = 'remote/' . date('Y') . '/' . date('m') . '/' . rand(1, 10000) . time() . strrchr($imgUrl, '.');
                $result = $Storage->write($domain, $destFileName, $img, -1);
                Log::write('$destFileName:' . $destFileName);
                if ($result) {
                    array_push($tmpNames, $result);
                } else {
                    array_push($tmpNames, "not supported");
                }

            }

        }
        /**
         * 返回数据格式
         * {
         *   'url'   : '新地址一ue_separate_ue新地址二ue_separate_ue新地址三',
         *   'srcUrl': '原始地址一ue_separate_ue原始地址二ue_separate_ue原始地址三'，
         *   'tip'   : '状态提示'
         * }
         */
        $return_data['url'] = implode("ue_separate_ue", $tmpNames);
        $return_data['tip'] = '远程图片抓取成功！';
        $return_data['srcUrl'] = $uri;
        $this->ajaxReturn($return_data);
    }

    /**
     * 无需移植
     * @function getMovie
     */
    public function getMovie()
    {
        $key = C("tudouSearchKey");
        $type = I('post.videoType');
        $html = file_get_contents('http://api.tudou.com/v3/gw?method=item.search&appKey=myKey&format=json&kw=' .
        $key . '&pageNo=1&pageSize=20&channelId=' . $type . '&inDays=7&media=v&sort=s');
        echo $html;
    }

    /**
     * @function imageManager
     */
    public function imageManager()
    {

        header("Content-Type: text/html; charset=utf-8");
        //需要遍历的目录列表，最好使用缩略图地址，否则当网速慢时可能会造成严重的延时
        $paths = array(UPLOAD_PATH, 'upload1/');
        $action = htmlspecialchars($_REQUEST["action"]);
        if ($action == "get") {
            if (!defined('SAE_TMP_PATH')) {
                $files = array();
                foreach ($paths as $path) {
                    $tmp = File::getFiles($path);
                    if ($tmp) {
                        $files = array_merge($files, $tmp);
                    }
                }
                if (!count($files)) return;
                rsort($files, SORT_STRING);
                $str = "";
                foreach ($files as $file) {
                    $str .= __ROOT__ . '/' . $file . "ue_separate_ue";
                }
                echo $str;
            } else {
                // SAE环境下
                $st = new \SaeStorage(); // 实例化
                /*
                *  getList:获取指定domain下的文件名列表
                *  return: 执行成功时返回文件列表数组，否则返回false
                *  参数：存储域，路径前缀，返回条数，起始条数
                */
                $num = 0;
                while ($ret = $st->getList(C('SaeStorage'), null, 100, $num)) {
                    foreach ($ret as $file) {
                        if (preg_match("/\.(gif|jpeg|jpg|png|bmp)$/i", $file))

                            echo $st->getUrl('upload', $file) . "ue_separate_ue";
                        $num++;
                    }
                }
            }
        }
    }

    /**
     * @function imageUp
     */
    public function imageUp()
    {
        // 上传图片框中的描述表单名称，
        $title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
        $path = htmlspecialchars($_POST['dir'], ENT_QUOTES);        
        $config = array(
            "savePath" => $this->savePath,
            "maxSize" =>  20000000, // 单位B
            "exts" => explode(",", 'gif,png,jpg,jpeg,bmp'),
            "subName" => $this->sub_name,
        );

        $upload = new Upload($config);
        $info = $upload->upload();
        if ($info) {
            $state = "SUCCESS";         
        } else {
            $state = "ERROR" . $upload->getError();
        }
        if(!isset($info['upfile'])){
        	$info['upfile'] = $info['Filedata'];
        }else{
        	//编辑器插入图片水印处理
        	// if($this->savePath=='Goods/'){
        	// 	$image = new \Think\Image();
        	// 	$water = tpCache('water');
        	// 	$imgresource = ".".$info['upfile']['urlpath'];
        	// 	$image->open($imgresource);
        	// 	if($water['is_mark']==1 && $image->width()>$water['mark_width'] && $image->height()>$water['mark_height']){
        	// 		if($water['mark_type'] == 'text'){
        	// 			$image->text($water['mark_txt'],'./hgzb.ttf',20,'#000000',9)->save($imgresource);
        	// 		}else{
        	// 			$image->water(".".$water['mark_img'],9,$water['mark_degree'])->save($imgresource);
        	// 		}
        	// 	}
        	// }
        }
        
        $return_data['url'] = $info['upfile']['urlpath'];
        $return_data['title'] = $title;
        $return_data['original'] = $info['upfile']['name'];
        $return_data['state'] = $state;
        $this->ajaxReturn(array('res'=>$return_data),'json');
    }

}