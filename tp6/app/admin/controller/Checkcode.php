<?php
/**
 * User: jayinton
 * Date: 2020/9/21
 */

namespace app\admin\controller;


use think\facade\Request;
use app\admin\service\AdminConfigService;
use app\common\controller\AdminController;
use app\common\libs\checkcode\Checkcode as CheckcodeGenerator;

/**
 * 校验码
 *
 * @package app\admin\controller
 */
class Checkcode extends AdminController
{
    private const DEFAULT_FONT_SIZE = 12;

    public $noNeedLogin = ['index'];

    public function index()
    {
        $checkcode_type = (int) AdminConfigService::getInstance()->getConfig('checkcode_type')['data'];
        $checkcode = new CheckcodeGenerator($checkcode_type);
        // 验证码类型
        $type = Request::param('type', 'verify');
        $checkcode->type = is_scalar($type) ? strtolower((string) $type) : 'verify';

        // 限制长度、字体大小与图片宽高范围，防止未登录恶意大图 DoS
        $checkcode->codelen = CheckcodeGenerator::normalizeCodeLen(Request::param('code_len', null));
        $checkcode->fontsize = CheckcodeGenerator::normalizeFontSize(
            Request::param('font_size', self::DEFAULT_FONT_SIZE),
            self::DEFAULT_FONT_SIZE
        );
        $checkcode->width = CheckcodeGenerator::normalizeWidth(Request::param('width', null));
        $checkcode->height = CheckcodeGenerator::normalizeHeight(Request::param('height', null));

        // 设置背景颜色
        $background = Request::param('background', '');
        if (is_string($background) && trim($background) !== '') {
            $checkcode->background = CheckcodeGenerator::normalizeHexColor(
                $background,
                CheckcodeGenerator::DEFAULT_BACKGROUND
            );
        }
        // 设置字体颜色
        $fontcolor = Request::param('font_color', '');
        if (is_string($fontcolor) && trim($fontcolor) !== '') {
            $checkcode->fontcolor = CheckcodeGenerator::normalizeHexColor($fontcolor, '');
        }

        // 显示验证码
        $refresh = Request::param('refresh', false);
        $checkcode->output($refresh);
    }

}
