# phpCaptcha
php image captcha , need GD extension.

## require
Predis

## install

    composer.json
    
    require: {
        "php-captcha/captcha": "~1.0"
    }

## example

* use base64 string

        use Captcha\CaptchaManager
        
        $redisClient = ...;
        $identity = ...;
        
        $captcha = new CaptchaManager($redisClient);
        
        $base64ImageString = $captcha->createBase64ImageCaptcha($identity);
        
        $response = ...;
        
        $response->setBody($base64ImageString);
        
        return $response;
        
        
* use content-type:image/png

        use Captcha\CaptchaManager
        
        $redisClient = ...;
        $identity = ...;
        
        $captcha = new CaptchaManager($redisClient);
        
        $captcha->createStreamImageCaptcha($identity);
        
        $response = ...;
        
        $response->headers->add([Content-type' => 'image/png']);
        
        return $response;