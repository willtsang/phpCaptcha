<?php

namespace Captcha;

use Predis\Client;

/**
 * Class CaptchaManager
 *
 * 验证码使用类
 */
class CaptchaManager
{
    private $sign = 'captcha:';

    private $container;

    /** @var  Client */
    private $redisHandle;

    /**
     * @var int 验证码在redis中的失效时间
     */
    private $ttl = 300;

    /**
     * CaptchaManager constructor.
     *
     * @param Client $client
     * @param int|null $ttl
     */
    public function __construct(Client $client, $ttl = null)
    {
        $this->redisHandle = $client;

        if (!empty($ttl)) {
            $this->ttl = $ttl;
        }
    }

    /**
     * 设置图片验证码验证码, 有5min的过期时间
     *
     * 该方法只当请求图片验证码接口使用, $code的产生依赖于图片验证码生成类
     *
     *
     * @param string $identity 一般为手机号
     * @param string $code 验证码,一般为4位字符
     */
    protected function set($identity, $code)
    {
        $this->redisHandle->setex($this->sign . $identity, $this->ttl, $code);
    }

    /**
     * 创建图片验证码文件
     *
     * @param string $identity 一般为手机号
     * @return string base64图片信息
     */
    public function createBase64ImageCaptcha($identity)
    {
        $pictureCaptcha = new PictureCaptcha();

        $image = $pictureCaptcha->create();
        $code = $pictureCaptcha->getCode();

        $this->set($identity, $code);

        $imageFile = $this->container->getParameter('kernel.cache_dir');
        $imageFile = $imageFile . DIRECTORY_SEPARATOR . $identity . 'png';

        imagepng($image, $imageFile);
        imagedestroy($image);//显示和销毁是必需的

        $base64Content = chunk_split(base64_encode(file_get_contents($imageFile)));//base64编码

        unlink($imageFile);

        // 解析方法
        /*
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64ImageDataString, $result)){
            $type = $result[2];
            $new_file = $fileTempDir . "/test.{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64ImageDataString)))){
                echo '新文件保存成功：', $new_file;
            }
        }
        */

        return 'data:image/png;base64,' . $base64Content;
    }


    /**
     * 创建图片验证码文件
     * 在Response头必须信息中指明`Content-type:image/png`
     *
     * @param string $identity 一般为手机号
     */
    public function createStreamImageCaptcha($identity)
    {
        $pictureCaptcha = new PictureCaptcha();

        $image = $pictureCaptcha->create();
        $code = $pictureCaptcha->getCode();

        $this->set($identity, $code);

        imagepng($image);
        imagedestroy($image);//显示和销毁是必需的
    }

    /**
     * 验证验证码
     *
     * @param string $identity 待验证
     * @param string $code 待验证验证码
     * @return bool
     */
    public function verify($identity, $code)
    {
        $correctCode = $this->redisHandle->get($this->sign . $identity);

        if (strcasecmp($correctCode, $code) == 0) {
            $this->del($identity);
            return true;
        }

        return false;
    }

    /**
     * 销毁验证码
     * (当验证成功时销毁)
     *
     * @param $identity
     */
    protected function del($identity)
    {
        $this->redisHandle->del($this->sign . $identity);
    }
}
