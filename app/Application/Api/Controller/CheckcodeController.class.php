<?php

// +----------------------------------------------------------------------
// | 验证码处理
// +----------------------------------------------------------------------

namespace Api\Controller;

use Common\Controller\CMS;

class CheckcodeController extends CMS {

	public function index() {
		$checkcode = new \Checkcode();
		//验证码类型
		$checkcode->type = I('get.type', 'verify', 'strtolower');
		//设置长度
		$codelen = I('get.code_len', 0, 'intval');
		if ($codelen) {
			if ($codelen > 8 || $codelen < 2) {
				$codelen = 4;
			}
			$checkcode->codelen = $codelen;
		}
		//设置验证码字体大小
		$fontsize = I('get.font_size', 0, 'intval');
		if ($fontsize) {
			$checkcode->fontsize = $fontsize;
		}
		//设置验证码图片宽度
		$width = I('get.width', 0, 'intval');
		if ($width) {
			$checkcode->width = $width;
		}
		//设置验证码图片高度
		$height = I('get.height', 0, 'intval');
		if ($height) {
			$checkcode->height = $height;
		}
		//设置背景颜色
		$background = I('get.background', '', '');
		if ($background) {
			$checkcode->background = $background;
		}
		//设置字体颜色
		$fontcolor = I('get.font_color', '', '');
		if ($fontcolor) {
			$checkcode->fontcolor = $fontcolor;
		}

		//显示验证码
		$checkcode->output(I('refresh', false, ''));
		return true;
	}

	/**
	 * 验证输入，看它是否生成的代码相匹配。
	 * @param type $type
	 * @param type $input
	 * @return type
	 */
	public function validate($type, $input) {
		$checkcode = new \Checkcode();
		$checkcode->type = $type;
		return $checkcode->validate($input, false);
	}

}
