<?php

include dirname(__FILE__).'/../vendor/autoload.php';

use Ligrila\Payment\Redsys;
use Ligrila\Payment\RedsysConfig;

function _log($result)
{
    $file = fopen('response_log.txt', 'w');
    fwrite($file, "REQUEST JSON:\n");
    fwrite($file, json_encode($_REQUEST));
    fwrite($file, "\n\n");
    fwrite($file, "Parsed Result\n");
    fwrite($file, json_encode($result));
}

$checkoutUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$responseUrl = dirname($checkoutUrl).'/response.php';

$config = new RedsysConfig(
    array(
        'debug' => true,
        'autoRedirect' => false,
        'checkoutLoading' => 'Click on checkout button',
        'checkoutText' => 'Checkout',
        'Ds_Merchant_MerchantCode' => '111111',
        'Ds_Merchant_Terminal' => '2',
        'Ds_Merchant_Password' => 'password',
        'Ds_Merchant_MerchantURL' => $responseUrl,

    )
);

$redsys = new Redsys($config);

$result = $redsys->parseResponse();

_log($result); //log to file, because this request is made in background

if ($response['accepted']) {
    //payment accepted
} else {
    //payment refused
}
