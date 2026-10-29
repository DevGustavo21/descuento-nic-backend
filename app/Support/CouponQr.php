<?php

namespace App\Support;

use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class CouponQr
{
    public static function redeemUrl(string $code): string
    {
        return url('/negocio/canjear/'.strtoupper($code));
    }

    public static function svg(string $payload): string
    {
        $options = new QROptions;
        $options->outputType = QROutputInterface::MARKUP_SVG;
        $options->outputBase64 = false;
        $options->scale = 6;
        $options->svgAddXmlHeader = false;
        $options->quietzoneSize = 2;

        return (new QRCode($options))->render($payload);
    }
}
