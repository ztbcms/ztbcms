<?php

namespace app;

use think\App;
use think\exception\ValidateException;
use think\response\Json;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
    }

    /**
     * 验证数据
     * @access protected
     * @param  array  $data  数据
     * @param  string|array  $validate  验证器名或者验证规则数组
     * @param  array  $message  提示信息
     * @param  bool  $batch  是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 创建统一的返回结果
     *
     * @param  boolean  $status  返回状态
     * @param  array  $data  返回数据
     * @param  string  $msg  返回提示
     * @param  string  $code  错误码
     * @param  string  $url  下一跳地址
     *
     * @return array
     */
    static function createReturn($status, $data = [], $msg = '', $code = null, $url = '')
    {
        //默认成功则为200 错误则为400
        if (empty($code)) {
            $code = $status ? 200 : 400;
        }
        return [
            'status' => $status,
            'code'   => $code,
            'data'   => $data,
            'msg'    => $msg,
            'url'    => $url,
        ];
    }

    /**
     * 构建json响应对象
     * @param  boolean  $status  返回状态
     * @param  array  $data  返回数据
     * @param  string  $msg  返回提示
     * @param  string  $code  错误码
     * @param  string  $url  下一跳地址
     *
     * @return Json
     */
    static function makeJsonReturn($status, $data = [], $msg = '', $code = null, $url = ''): Json
    {
        return json(self::createReturn($status, $data, $msg, $code, $url));
    }

    /**
     * 获取成功状态的json返回
     * @param  array  $data
     * @param  string  $msg
     * @param  null  $code
     * @param  string  $url
     * @return Json
     */
    static function returnSuccessJson(array $data = [], string $msg = '', $code = null, string $url = ''): Json
    {
        return self::makeJsonReturn(true, $data, $msg, $code, $url);
    }

    /**
     * 获取失败状态的json返回
     * @param  string  $msg
     * @param  array  $data
     * @param  null  $code
     * @param  string  $url
     * @return Json
     */
    static function returnErrorJson(string $msg = '', array $data = [], $code = null, string $url = ''): Json
    {
        return self::makeJsonReturn(false, $data, $msg, $code, $url);
    }
}
