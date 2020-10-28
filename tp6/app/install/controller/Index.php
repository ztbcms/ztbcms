<?php
/**
 * User: jayinton
 */

namespace app\install\controller;


use app\admin\libs\helper\MysqlHelper;
use app\BaseController;
use app\common\util\Dir;

class Index extends BaseController
{
    function index(){
        return view('index');
    }


    function step2(){
        //错误
        $err = 0;
        //mysql检测
        $db_version = MysqlHelper::getVersion();
        if(!empty($db_version)){
            $mysql = '<span class="correct_span">&radic;</span> ' . $db_version;
        }else{
            $mysql = '<span class="correct_span error_span">&radic;</span> 链接错误' ;
        }

        //上传检测
        if (ini_get('file_uploads')) {
            $uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
        } else {
            $uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
            $err++;
        }
        //session检测
        if (function_exists('session_start')) {
            $session = '<span class="correct_span">&radic;</span> 支持';
        } else {
            $session = '<span class="correct_span error_span">&radic;</span> 不支持';
            $err++;
        }
        //目录权限检测
        $folder = array(
            'public/',
            'app/install/',
            'config/',
        );
        $folderInfo = array();
        foreach ($folder as $dir) {
            $result = array(
                'dir' => $dir,
            );
            $path = root_path() . $dir;
            //是否可读
            if (is_readable($path)) {
                $result['is_readable'] = '<span class="correct_span">&radic;</span>可读';
            } else {
                $result['is_readable'] = '<span class="correct_span error_span">&radic;</span>不可读';
                $err++;
            }
            //是否可写
            if (is_writable($path)) {
                $result['is_writable'] = '<span class="correct_span">&radic;</span>可写';
            } else {
                $result['is_writable'] = '<span class="correct_span error_span">&radic;</span>不可写';
                $err++;
            }
            $folderInfo[] = $result;
        }
        //PHP内置函数检测
        $function = array(
            [
                'name' => 'mb_strlen',
                'value'=> function_exists('mb_strlen')
            ],
            [
                'name' => 'curl_init',
                'value'=> function_exists('curl_init')
            ],
        );
        foreach ($function as $rs) {
            if ($rs == false) {
                $err++;
            }
        }


        return view('step2', [
            'os'         => PHP_OS,
            'function'   => $function,
            'err'        => $err,
            'phpv'       => @phpversion(),
            'mysql'      => $mysql,
            'uploadSize' => $uploadSize,
            'session'    => $session,
            'folderInfo' => $folderInfo
        ]);
    }
    function step3(){
        return view('step3');
    }


    function step4(){
        return view('step4');
    }


}