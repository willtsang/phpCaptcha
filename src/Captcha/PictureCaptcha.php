<?php

namespace Captcha;

/**
 * Class SampleLib
 *
 * 简单的图片验证码
 *
 * 使用：
 *   $im = new SampleLib();
 *   $im = $im->create();
 *   header('Content-type:image/png');
 *   imagepng($im);
 *   imagedestroy($im);//显示和销毁是必需的。
 *
 */
class PictureCaptcha
{
    /**
     * @var int 图片像素宽度
     */
    protected $imageWidth;

    /**
     * @var int 图片像素高度
     */
    protected $imageHeight;


    /**
     * @var resource 图片句柄
     */
    protected $imageResource;


    /**
     * @var string 字符集
     */
    protected $font;

    /**
     * @var string 字体文件路径
     */
    protected $fontDir;

    /**
     * @var int 字符尺寸大小
     */
    protected $fontSize;

    /**
     * @var string 验证码
     */
    protected $code;

    /**
     * @var int 验证码字符数
     */
    protected $codeCount;

    /**
     * @var array 颜色数组
     */
    protected $colorArr;

    /**
     * @var int 图片中线的数量
     */
    protected $lineCount;

    /**
     * @var int 图片中点的数量
     */
    protected $pixCount;
    /**
     * @var int 颜色的数量
     */
    protected $colorCount;

    /**
     * @var array 颜色值
     */
    protected $colorStyle;

    /**
     * @var array 配置参数
     */
    protected $config;

    /**
     * 生成图片验证码需GD库支持
     */
    public function __construct()
    {
        if (!function_exists('imagecreate')) {
            throw new \RuntimeException('GD extension is not exist!');
        }

        $this->init();
    }


    public function init()
    {
        $this->config = $this->getConfig();

        $this->font = empty($this->config['font']) ? $this->config['font'] : '0123456789';
        $this->fontDir = dirname(__FILE__) . '/' . (empty($this->config['fontFile']) ? $this->config['fontFile'] : 'BELL.TTF');
        $this->imageWidth = empty($this->config['width']) ? $this->config['width'] : 100;
        $this->imageHeight = empty($this->config['height']) ? $this->config['height'] : 30;
        $this->codeCount = empty($this->config['codeCount']) ? $this->config['codeCount'] : 4;
        $this->fontSize = empty($this->config['fontSize']) ? $this->config['fontSize'] : 30;
        $this->lineCount = empty($this->config['lineCount']) ? $this->config['lineCount'] : 2;
        $this->pixCount = empty($this->config['pixCount']) ? $this->config['pixCount'] : 200;
        $this->colorCount = empty($this->config['colorCount']) ? $this->config['colorCount'] : 1;
        $this->colorStyle = empty($this->config['colorStyle']) ? $this->config['colorStyle'] : [];
    }


    /**
     * 载入配置参数
     *
     * @return array
     */
    public function getConfig()
    {
        $fileDir = dirname(__FILE__) . '/config.php';

        if (file_exists($fileDir)) {
            return require $fileDir;
        }

        return [];
    }

    /**
     * 返回创建好的图像句柄，
     * 还需要进行头信息的设置与销毁
     *
     * @return resource $this->img
     */
    public function create()
    {
        $this->createImage();
        $this->createColor($this->colorCount);
        $this->createPix($this->pixCount);
        $this->createLine($this->lineCount);
        $this->createText();

        return $this->imageResource;
    }


    /**
     * 创建字符所用颜色
     * 可以指定颜色的个数
     * 默认为4个颜色
     * @param int $colorCount
     * @return array
     */
    protected function createColor($colorCount = 0)
    {
        $this->colorArr = [];

        // 第一次调用该函数则 为画布创建背景色
        imagecolorallocate($this->imageResource, 255, 255, 255);

        if ($colorCount > 0) {

            $i = 0;

            $styleIndex = 0;
            $styleCount = count($this->colorStyle) - 1;


            while ($i < $colorCount) {

                if (isset($this->colorStyle[$styleIndex])) {
                    $color1 = $this->colorStyle[$styleIndex][0];
                    $color2 = $this->colorStyle[$styleIndex][1];
                    $color3 = $this->colorStyle[$styleIndex][2];

                    // 当设置的颜色不够时，循环赋值
                    if ($styleIndex == $styleCount) {
                        $styleIndex = 0;
                    } else {
                        $styleIndex++;
                    }
                } else {
                    $color1 = rand(0, 255);
                    $color2 = rand(0, 255);
                    $color3 = rand(0, 255);
                }

                $this->colorArr[] = imagecolorallocate($this->imageResource, $color1, $color2, $color3);
                $i++;
            }

            return $this->colorArr;
        }

        $this->colorArr[] = imagecolorallocate($this->imageResource, 0, 0, 0);
        $this->colorArr[] = imagecolorallocate($this->imageResource, 0, 0, 0);
        $this->colorArr[] = imagecolorallocate($this->imageResource, 0, 0, 0);
        $this->colorArr[] = imagecolorallocate($this->imageResource, 0, 0, 0);

        return $this->colorArr;
    }


    /**
     * 创建基础画布
     */
    protected function createImage()
    {
        $this->imageResource = imagecreate($this->imageWidth, $this->imageHeight);
    }

    /**
     * 在画布上生成若干点
     * （颜色随机，位置随机）
     *
     * @param int $pixCount 生成点的数量
     */
    protected function createPix($pixCount = 400)
    {
        $i = 0;
        $colorIndex = count($this->colorArr) - 1;

        while ($i < $pixCount) {
            imagesetpixel($this->imageResource, rand(0, $this->imageWidth), rand(0, $this->imageHeight), $this->colorArr[rand(0, $colorIndex)]);
            $i++;
        }
    }


    /**
     * 画线
     *
     * @param int $lineCount 线的数量
     */
    protected function createLine($lineCount = 2)
    {
        $i = 0;
        imagesetstyle($this->imageResource, $this->colorArr);

        while ($i < $lineCount) {
            imageline($this->imageResource, 0, rand(0, $this->imageHeight), $this->imageWidth, rand(0, $this->imageHeight), IMG_COLOR_STYLED);
            $i++;
        }
    }

    /**
     * 设置图片中文字
     */
    protected function createText()
    {
        $i = 0;
        $fontIndex = strlen($this->font) - 1;
        $colorIndex = count($this->colorArr) - 1;
        $codes = '';
        $x = 0;

        while ($i < $this->codeCount) {

            $code = $this->font{rand(0, $fontIndex)};
            $color = $this->colorArr[rand(0, $colorIndex)];

            imagettftext($this->imageResource, $this->fontSize, 0, $x, rand($this->fontSize - 10, $this->fontSize * 2 - $this->imageHeight), $color, $this->fontDir, $code);
            $x += intval($this->imageWidth / $this->codeCount);

            $i++;
            $codes .= $code;
        }

        $this->code = $codes;
    }

    /**
     * 获取code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
