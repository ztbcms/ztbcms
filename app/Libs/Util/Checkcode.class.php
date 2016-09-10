<?php

// +----------------------------------------------------------------------
// | 验证码生成类
// +----------------------------------------------------------------------

class Checkcode {

	//随机因子
	private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';
	//验证码
	private $code;
	//验证码长度
	private $codelen = 4;
	//宽度
	private $width = 100;
	//高度
	private $height = 30;
	//图形资源句柄
	private $img;
	//指定的字体
	private $font;
	//指定字体大小
	private $fontsize = 15;
	//指定字体颜色
	private $fontcolor;
	//设置背景色
	private $background = '#EDF7FF';
	//验证码类型
	private $type = '';
	//输出多少次后更换验证码
	private $testLimit = 3;

	//构造方法初始化
	public function __construct() {
		$this->font = COMMON_PATH . 'Font/elephant.ttf';
		$checkcode_type = (int) cache('Config.checkcode_type');
		switch ($checkcode_type) {
			//纯数字
			case 1:
				$this->charset = '0123456789';
				break;
			//纯字母
			case 2:
				$this->charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ';
				break;
		}
	}

	//魔术方法，设置
	public function __set($name, $value) {
		if (empty($name) || in_array($name, array('code', 'img'))) {
			return false;
		}
		$this->$name = $value;
	}

	//生成随机码
	private function createCode() {
		$code = '';
		$_len = strlen($this->charset) - 1;
		for ($i = 0; $i < $this->codelen; $i++) {
			$code .= $this->charset[mt_rand(0, $_len)];
		}
		return $code;
	}

	//生成背景
	private function createBg() {
		$this->img = imagecreatetruecolor($this->width, $this->height);
		if (empty($this->background)) {
			$color = imagecolorallocate($this->img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
		} else {
			//设置背景色
			$color = imagecolorallocate($this->img, hexdec(substr($this->background, 1, 2)), hexdec(substr($this->background, 3, 2)), hexdec(substr($this->background, 5, 2)));
		}
		imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);
	}

	//生成文字
	private function createFont() {
		$_x = $this->width / $this->codelen;
		$isFontcolor = false;
		if ($this->fontcolor && !$isFontcolor) {
			$this->fontcolor = imagecolorallocate($this->img, hexdec(substr($this->fontcolor, 1, 2)), hexdec(substr($this->fontcolor, 3, 2)), hexdec(substr($this->fontcolor, 5, 2)));
			$isFontcolor = true;
		}
		for ($i = 0; $i < $this->codelen; $i++) {
			if (!$isFontcolor) {
				$this->fontcolor = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
			}
			imagettftext($this->img, $this->fontsize, mt_rand(-30, 30), $_x * $i + mt_rand(1, 5), $this->height / 1.4, $this->fontcolor, $this->font, $this->code[$i]);
		}
	}

	//生成线条、雪花
	private function createLine() {
		for ($i = 0; $i < 6; $i++) {
			$color = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
			imageline($this->img, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $color);
		}
		for ($i = 0; $i < 100; $i++) {
			$color = imagecolorallocate($this->img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
			imagestring($this->img, mt_rand(1, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $color);
		}
	}

	//输出
	public function output($regenerate = false) {
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header('Content-type:image/png');
		$this->createBg();
		$this->getVerifyCode($regenerate);
		$this->createLine();
		$this->createFont();
		imagepng($this->img);
		imagedestroy($this->img);
	}

	/**
	 * $regenerate
	 * @param type $regenerate 刷新
	 * @return type
	 */
	protected function getVerifyCode($regenerate = false) {
		$name = $this->getSessionKey();
		$old = session($name);
		//没有的话重新生成个
		if (empty($old) || $regenerate) {
			$this->code = $this->createCode();
			session($name, $this->code);
			session($name . 'count', 1);
		} else {
			$this->code = $old;
		}
		return $this->code;
	}

	//获取验证码
	public function getCode() {
		return strtolower($this->getVerifyCode());
	}

	/**
	 * 验证输入，看它是否生成的代码相匹配。
	 * @param type $input 用户输入的验证码
	 * @param type $caseSensitive 是否验证大小写
	 * @return boolean
	 */
	public function validate($input, $caseSensitive = false) {
		$code = $this->getVerifyCode();
		$valid = $caseSensitive ? ($input === $code) : strcasecmp($input, $code) === 0;
		$name = $this->getSessionKey() . 'count';
		$old = session($name);
		$session = (int) $old + 1;
		session($name, $session);
		if ($session > $this->testLimit || $valid) {
			$this->getVerifyCode(true);
		}
		return $valid;
	}

	//返回用于存储验证码的会话变量名。
	protected function getSessionKey() {
		return md5($this->type);
	}

}
