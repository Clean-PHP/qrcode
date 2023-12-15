<?php
/**
 * 生成带LOGO图片的二维码 演示
 * @FileName: demo.php
 * @Author: tekin
 * @QQ: 3316872019
 * @Email: tekintian@gmail.com
 * @Supported: http://dev.yunnan.ws/
 * @Date:   2017-05-28 13:21:38
 * @Last Modified 2017-05-28
 */

namespace library\qrcode;

use cleanphp\App;
use cleanphp\base\Variables;
use cleanphp\file\Log;
use ErrorException;
use library\qrcode\src\Common\EccLevel;
use library\qrcode\src\Common\Version;
use library\qrcode\src\Data\QRMatrix;
use library\qrcode\src\QRCode;
use library\qrcode\src\QROptions;
use Throwable;

class Code
{


    static function encode(string $data, string $logo)
    {
        $data = empty($data) ? "empty data" : $data;
        $options = new QROptions([
            'version' => Version::AUTO,
            'eccLevel' => EccLevel::H,
            'imageBase64' => false,
            'addLogoSpace' => strlen($data) > 25,
            'logoSpaceWidth' => 13,
            'logoSpaceHeight' => 13,
            'scale' => 6,
            'imageTransparent' => false,
            'drawCircularModules' => true,
            'circleRadius' => 0.45,
            'keepAsSquare' => [QRMatrix::M_FINDER, QRMatrix::M_FINDER_DOT],
            'returnResource' => true
        ]);

        $qrcode = new QRCode($options);
        $qrcode->addByteSegment($data);


        try {
            $path = parse_url($logo, PHP_URL_PATH);

            $extension = pathinfo($path, PATHINFO_EXTENSION);

            $image = Variables::getCachePath("logo.$extension");
            file_put_contents($image, file_get_contents($logo));


            header('Content-type: image/png');

            echo (new QRImageWithLogo($options, $qrcode->getQRMatrix()))->dump(null, $image);

        } catch (ErrorException|src\Data\QRCodeDataException|src\Output\QRCodeOutputException $e) {
            Log::record("Qrcode", $e->getMessage(), Log::TYPE_ERROR);
            App::exit("Image Error");
        }

        App::exit("Out put image");
    }

    static function decode($path): string
    {
        try {
            $result = (new QRCode)->readFromFile($path);
        } catch (src\Decoder\QRCodeDecoderException|Throwable $e) {
            Log::record("Qrcode", $e->getMessage(), Log::TYPE_ERROR);
            return "";
        } // -> DecoderResult
        return (string)$result;
    }
}
