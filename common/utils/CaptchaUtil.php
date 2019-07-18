<?php
/**
 * User: zp
 * Date: 19/7/15
 * Time: 下午4:38
 */

namespace common\utils;


use Yii;
use yii\base\InvalidConfigException;

class CaptchaUtil
{
    public $minLength = 5;
    public $maxLength = 5;
    public $testLimit = 3;
    public $width = 120;
    public $height = 50;
    public $padding = 2;
    public $backColor = 0xFFFFFF;
    public $foreColor = 0x2040A0;
    public $transparent = false;
    public $offset = -2;
    public $fontFile = '@yii/captcha/SpicyRice.ttf';
    public $fixedVerifyCode;
    public $imageLibrary;

    public static function create()
    {
        $model = new self();
        // 设置字体
        $model->initFont();
        // 获得验证码
        $code = $model->generateVerifyCode();
        // 获得二进制图片
        $content = $model->renderImage($code);
        // 转化为base64的图片
        $src = 'data:' . 'image/png' . ';base64,' . base64_encode($content);

        return [$code, $src];
    }

    public function initFont()
    {
        $this->fontFile = Yii::getAlias($this->fontFile);
        if (!is_file($this->fontFile)) {
            throw new InvalidConfigException("The font file does not exist: {$this->fontFile}");
        }
    }

    /**
     * Generates a new verification code.
     */
    public function generateVerifyCode()
    {
        if ($this->minLength > $this->maxLength) {
            $this->maxLength = $this->minLength;
        }
        if ($this->minLength < 3) {
            $this->minLength = 3;
        }
        if ($this->maxLength > 20) {
            $this->maxLength = 20;
        }
        $length = mt_rand($this->minLength, $this->maxLength);

        $letters = 'bcdfghjklmnpqrstvwxyz';
        $vowels = 'aeiou';
        $code = '';
        for ($i = 0; $i < $length; ++$i) {
            if ($i % 2 && mt_rand(0, 10) > 2 || !($i % 2) && mt_rand(0, 10) > 9) {
                $code .= $vowels[mt_rand(0, 4)];
            } else {
                $code .= $letters[mt_rand(0, 20)];
            }
        }

        return $code;
    }

    /**
     * Renders the CAPTCHA image.
     * @param string $code the verification code
     * @return string image contents
     * @throws InvalidConfigException if imageLibrary is not supported
     */
    public function renderImage($code)
    {
        if (isset($this->imageLibrary)) {
            $imageLibrary = $this->imageLibrary;
        } else {
            $imageLibrary = self::checkRequirements();
        }
        if ($imageLibrary === 'gd') {
            return $this->renderImageByGD($code);
        } elseif ($imageLibrary === 'imagick') {
            return $this->renderImageByImagick($code);
        } else {
            throw new InvalidConfigException("Defined library '{$imageLibrary}' is not supported");
        }
    }

    public static function checkRequirements()
    {
        if (extension_loaded('imagick')) {
            $imagickFormats = (new \Imagick())->queryFormats('PNG');
            if (in_array('PNG', $imagickFormats, true)) {
                return 'imagick';
            }
        }
        if (extension_loaded('gd')) {
            $gdInfo = gd_info();
            if (!empty($gdInfo['FreeType Support'])) {
                return 'gd';
            }
        }
        throw new InvalidConfigException('Either GD PHP extension with FreeType support or ImageMagick PHP extension with PNG support is required.');
    }

    public function renderImageByGD($code)
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        $backColor = imagecolorallocate(
            $image,
            (int)($this->backColor % 0x1000000 / 0x10000),
            (int)($this->backColor % 0x10000 / 0x100),
            $this->backColor % 0x100
        );
        imagefilledrectangle($image, 0, 0, $this->width - 1, $this->height - 1, $backColor);
        imagecolordeallocate($image, $backColor);

        if ($this->transparent) {
            imagecolortransparent($image, $backColor);
        }

        $foreColor = imagecolorallocate(
            $image,
            (int)($this->foreColor % 0x1000000 / 0x10000),
            (int)($this->foreColor % 0x10000 / 0x100),
            $this->foreColor % 0x100
        );

        $length = strlen($code);
        $box = imagettfbbox(30, 0, $this->fontFile, $code);
        $w = $box[4] - $box[0] + $this->offset * ($length - 1);
        $h = $box[1] - $box[5];
        $scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
        $x = 10;
        $y = round($this->height * 27 / 40);
        for ($i = 0; $i < $length; ++$i) {
            $fontSize = (int)(rand(26, 32) * $scale * 0.8);
            $angle = rand(-10, 10);
            $letter = $code[$i];
            $box = imagettftext($image, $fontSize, $angle, $x, $y, $foreColor, $this->fontFile, $letter);
            $x = $box[2] + $this->offset;
        }

        imagecolordeallocate($image, $foreColor);

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return ob_get_clean();
    }

    public function renderImageByImagick($code)
    {
        $backColor = $this->transparent
            ? new \ImagickPixel('transparent')
            : new \ImagickPixel('#' . str_pad(dechex($this->backColor), 6, 0, STR_PAD_LEFT));
        $foreColor = new \ImagickPixel('#' . str_pad(dechex($this->foreColor), 6, 0, STR_PAD_LEFT));

        $image = new \Imagick();
        $image->newImage($this->width, $this->height, $backColor);

        $draw = new \ImagickDraw();
        $draw->setFont($this->fontFile);
        $draw->setFontSize(30);
        $fontMetrics = $image->queryFontMetrics($draw, $code);

        $length = strlen($code);
        $w = (int)$fontMetrics['textWidth'] - 8 + $this->offset * ($length - 1);
        $h = (int)$fontMetrics['textHeight'] - 8;
        $scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
        $x = 10;
        $y = round($this->height * 27 / 40);
        for ($i = 0; $i < $length; ++$i) {
            $draw = new \ImagickDraw();
            $draw->setFont($this->fontFile);
            $draw->setFontSize((int)(rand(26, 32) * $scale * 0.8));
            $draw->setFillColor($foreColor);
            $image->annotateImage($draw, $x, $y, rand(-10, 10), $code[$i]);
            $fontMetrics = $image->queryFontMetrics($draw, $code[$i]);
            $x += (int)$fontMetrics['textWidth'] + $this->offset;
        }

        $image->setImageFormat('png');
        return $image->getImageBlob();
    }

}
