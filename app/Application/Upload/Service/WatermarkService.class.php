<?php
/**
 * User: jayinton
 * Date: 2019-08-19
 * Time: 17:04
 */

namespace Upload\Service;


use Intervention\Image\ImageManagerStatic as Image;
use System\Service\BaseService;

/**
 * 水印服务
 * @package Upload\Service
 */
class WatermarkService extends BaseService
{

    /**
     * WatermarkService constructor.
     */
    public function __construct()
    {
    }

    /**
     * 获取水印配置
     * @return array
     */
    function getWatermarkConfig()
    {
        $system_configs = M('Config')->where([
            'varname' => ['IN', 'watermarkenable,watermarkminwidth,watermarkminheight,watermarkimg,watermarkpct,watermarkquality,watermarkpos']
        ])->select();
        $config = [];
        foreach ($system_configs as $i => $v) {
            $config[$v['varname']] = $v['value'];
        }
        //处理水印
        $watermark_config = [
            'enable' => intval($config['watermarkenable']),
            'position' => self::getPositionByNumber($config['watermarkpos']),
            'quality' => intval($config['watermarkquality']),
            'width' => intval($config['watermarkminwidth']),
            'height' => intval($config['watermarkminheight']),
            'opacity' => intval($config['watermarkpct']),//透明度
            'img_path' => $config['watermarkimg'],//水印图片路径
        ];

        return self::createReturn(true, $watermark_config);
    }

    /**
     * 添加水印
     * @param string $source_image_path 原图路径
     * @param string $save_image_path 保存路径
     * @param array $watermark_config 水印配置
     * @return array
     */
    function addWaterMark($source_image_path, $save_image_path, $watermark_config)
    {
        // create new Intervention Image
        $source_image = Image::make($source_image_path);

        //如果目标图片的宽高均大于水印图片才能打水印
        if ($source_image->width() >= $watermark_config['width'] && $source_image->height() >= $watermark_config['height']) {
            // create a new Image instance for inserting
            $watermark = Image::make(SITE_PATH . $watermark_config['img_path']);
            $watermark->resize($watermark_config['width'], $watermark_config['height']);
            $watermark->opacity($watermark_config['opacity']);

            // insert watermark
            $source_image->insert($watermark, $watermark_config['position'], 0, 0);
            $source_image->save($save_image_path, $watermark_config['quality']);
        }

        return self::createReturn(true, null, '操作成功');
    }

    /**
     * 水印位置 定义请看： http://image.intervention.io/api/insert
     * @param $waterPos
     * @return string
     */
    static function getPositionByNumber($waterPos)
    {
        $waterPos = intval($waterPos);
        switch ($waterPos) {
            case 1://1为顶端居左
                return 'top-left';
            case 2://2为顶端居中
                return 'top';
            case 3://3为顶端居右
                return 'top-right';
            case 4://4为中部居左
                return 'left';
            case 5://5为中部居中
                return 'center';
            case 6://6为中部居右
                return 'right';
            case 7://7为底端居左
                return 'bottom-left';
            case 8://8为底端居中
                return 'bottom';
            case 9://9为底端居右
                return 'bottom-right';
            default:
                return 'top-left';
        }
    }
}