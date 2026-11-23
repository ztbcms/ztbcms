<?php
/**
 * User: jayinton
 * Date: 2020/9/21
 */

namespace app\common\libs\checkcode;

/**
 * 验证码生成类
 */
class Checkcode
{
    public const MIN_CODE_LEN = 2;
    public const MAX_CODE_LEN = 8;
    public const DEFAULT_CODE_LEN = 4;

    public const MIN_WIDTH = 50;
    public const MAX_WIDTH = 400;
    public const DEFAULT_WIDTH = 100;

    public const MIN_HEIGHT = 20;
    public const MAX_HEIGHT = 200;
    public const DEFAULT_HEIGHT = 30;

    public const MIN_FONT_SIZE = 10;
    public const MAX_FONT_SIZE = 60;
    public const DEFAULT_FONT_SIZE = 15;

    public const DEFAULT_BACKGROUND = '#EDF7FF';

    //随机因子
    private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';
    //验证码
    private $code;
    //验证码长度
    private $codelen = self::DEFAULT_CODE_LEN;
    //宽度
    private $width = self::DEFAULT_WIDTH;
    //高度
    private $height = self::DEFAULT_HEIGHT;
    //图形资源句柄
    private $img;
    //指定的字体
    private $font;
    //指定字体大小
    private $fontsize = self::DEFAULT_FONT_SIZE;
    //指定字体颜色
    private $fontcolor;
    //设置背景色
    private $background = self::DEFAULT_BACKGROUND;
    //验证码类型
    private $type = '';
    //输出多少次后更换验证码
    private $testLimit = 3;

    /**
     * Checkcode constructor.
     *
     * @param  int|string  $checkcode_type  验证码类型
     */
    public function __construct($checkcode_type)
    {
        $this->font = app()->getRootPath().'app/common/libs/checkcode/font/elephant.ttf';
        switch ($checkcode_type) {
            //纯数字
            case 1:
                $this->charset = '0123456789';
                break;
            //纯字母
            case 2:
                $this->charset = 'abcdefghikjlmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            //字母+数字 3
            default:
                $this->charset = '0123456789abcdefghikjlmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }
    }

    /**
     * 规范化验证码长度
     *
     * @param  mixed  $value    待校验值
     * @param  int    $default  默认值
     *
     * @return int
     */
    public static function normalizeCodeLen($value, int $default = self::DEFAULT_CODE_LEN): int
    {
        return self::normalizeIntRange($value, self::MIN_CODE_LEN, self::MAX_CODE_LEN, $default);
    }

    /**
     * 规范化验证码图片宽度
     *
     * @param  mixed  $value    待校验值
     * @param  int    $default  默认值
     *
     * @return int
     */
    public static function normalizeWidth($value, int $default = self::DEFAULT_WIDTH): int
    {
        return self::normalizeIntRange($value, self::MIN_WIDTH, self::MAX_WIDTH, $default);
    }

    /**
     * 规范化验证码图片高度
     *
     * @param  mixed  $value    待校验值
     * @param  int    $default  默认值
     *
     * @return int
     */
    public static function normalizeHeight($value, int $default = self::DEFAULT_HEIGHT): int
    {
        return self::normalizeIntRange($value, self::MIN_HEIGHT, self::MAX_HEIGHT, $default);
    }

    /**
     * 规范化验证码字体大小
     *
     * @param  mixed  $value    待校验值
     * @param  int    $default  默认值
     *
     * @return int
     */
    public static function normalizeFontSize($value, int $default = self::DEFAULT_FONT_SIZE): int
    {
        return self::normalizeIntRange($value, self::MIN_FONT_SIZE, self::MAX_FONT_SIZE, $default);
    }

    /**
     * 规范化十六进制颜色值（#RRGGBB）
     *
     * @param  mixed   $value       待校验值
     * @param  string  $default     默认颜色
     * @param  bool    $allowEmpty  是否允许空字符串
     *
     * @return string
     */
    public static function normalizeHexColor($value, string $default = '', bool $allowEmpty = false): string
    {
        if (!is_string($value)) {
            return $default;
        }
        $trimmed = trim($value);
        if ($trimmed === '') {
            return $allowEmpty ? '' : $default;
        }
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $trimmed) === 1) {
            return $trimmed;
        }

        return $default;
    }

    /**
     * 在指定闭区间内校验并解析整数，不合法或越界时返回默认值
     *
     * @param  mixed  $value    待校验值
     * @param  int    $min      最小允许值
     * @param  int    $max      最大允许值
     * @param  int    $default  默认值
     *
     * @return int
     */
    private static function normalizeIntRange($value, int $min, int $max, int $default): int
    {
        // 避免 filter_var(true, FILTER_VALIDATE_INT) 返回 1 或数组/浮点静默截断
        if (is_bool($value)) {
            return $default;
        }
        if (is_int($value)) {
            $intVal = $value;
        } elseif (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || preg_match('/^[+-]?\d+$/', $trimmed) !== 1) {
                return $default;
            }
            $filtered = filter_var($trimmed, FILTER_VALIDATE_INT);
            if ($filtered === false) {
                return $default;
            }
            $intVal = (int) $filtered;
        } else {
            return $default;
        }

        if ($intVal < $min || $intVal > $max) {
            return $default;
        }

        return $intVal;
    }

    //魔术方法，设置
    public function __set($name, $value)
    {
        if (empty($name) || in_array($name, array('code', 'img'))) {
            return false;
        }
        switch ($name) {
            case 'codelen':
                $this->codelen = self::normalizeCodeLen($value);
                return;
            case 'width':
                $this->width = self::normalizeWidth($value);
                return;
            case 'height':
                $this->height = self::normalizeHeight($value);
                return;
            case 'fontsize':
                $this->fontsize = self::normalizeFontSize($value);
                return;
            case 'background':
                $this->background = self::normalizeHexColor($value, self::DEFAULT_BACKGROUND, true);
                return;
            case 'fontcolor':
                $this->fontcolor = self::normalizeHexColor($value, '', true);
                return;
            case 'type':
                $this->type = is_scalar($value) ? (string) $value : '';
                return;
            default:
                $this->$name = $value;
        }
    }

    //生成随机码
    private function createCode()
    {
        $this->codelen = self::normalizeCodeLen($this->codelen);
        $code = '';
        $_len = strlen($this->charset) - 1;
        for ($i = 0; $i < $this->codelen; $i++) {
            $code .= $this->charset[mt_rand(0, $_len)];
        }
        return $code;
    }

    //生成背景
    private function createBg()
    {
        $this->width = self::normalizeWidth($this->width);
        $this->height = self::normalizeHeight($this->height);
        $background = self::normalizeHexColor($this->background, self::DEFAULT_BACKGROUND, true);

        $this->img = imagecreatetruecolor($this->width, $this->height);
        if (empty($background)) {
            $color = imagecolorallocate($this->img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        } else {
            //设置背景色
            $color = imagecolorallocate($this->img, hexdec(substr($background, 1, 2)), hexdec(substr($background, 3, 2)), hexdec(substr($background, 5, 2)));
        }
        imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);
    }

    //生成文字
    private function createFont()
    {
        $this->width = self::normalizeWidth($this->width);
        $this->height = self::normalizeHeight($this->height);
        $this->codelen = self::normalizeCodeLen($this->codelen);
        $this->fontsize = self::normalizeFontSize($this->fontsize);

        $_x = $this->width / $this->codelen;
        $hexFontColor = self::normalizeHexColor($this->fontcolor, '', true);
        $isFontcolor = false;
        $fontColor = null;
        if ($hexFontColor !== '') {
            $fontColor = imagecolorallocate($this->img, hexdec(substr($hexFontColor, 1, 2)), hexdec(substr($hexFontColor, 3, 2)), hexdec(substr($hexFontColor, 5, 2)));
            $isFontcolor = true;
        }
        for ($i = 0; $i < $this->codelen; $i++) {
            if (!$isFontcolor) {
                $fontColor = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            }
            imagettftext($this->img, $this->fontsize, mt_rand(-30, 30), (int) ($_x * $i + mt_rand(1, 5)), (int) ($this->height / 1.4), $fontColor, $this->font, $this->code[$i]);
        }
    }

    //生成线条、雪花
    private function createLine()
    {
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
    public function output($regenerate = false)
    {
        ob_clean();
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
     * 生成验证码
     * $regenerate
     *
     * @param  boolean  $regenerate  刷新
     *
     * @return string
     */
    protected function getVerifyCode($regenerate = false)
    {
        $name = $this->getSessionKey();
        $old = session($name);
        //没有的话重新生成个
        if (empty($old) || $regenerate) {
            $this->code = $this->createCode();
            session($name, $this->code);
            session($name.'count', 1);
        } else {
            $this->code = $old;
        }
        return $this->code;
    }

    //获取验证码
    public function getCode()
    {
        return strtolower($this->getVerifyCode());
    }

    /**
     * 验证输入，看它是否生成的代码相匹配。
     *
     * @param  string  $input  用户输入的验证码
     * @param  boolean  $caseSensitive  是否验证大小写
     *
     * @return boolean
     */
    public function validate($input, $caseSensitive = false)
    {
        $code = $this->getVerifyCode();
        $valid = $caseSensitive ? ($input === $code) : strcasecmp($input, $code) === 0;
        $name = $this->getSessionKey().'count';
        $old = session($name);
        $session = (int) $old + 1;
        session($name, $session);
        if ($session > $this->testLimit || $valid) {
            $this->getVerifyCode(true);
        }
        return $valid;
    }

    //返回用于存储验证码的会话变量名。
    protected function getSessionKey()
    {
        return md5($this->type);
    }
}
