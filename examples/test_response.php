<?php

// this example uses the file output in response.php for debug purposes

include dirname(__FILE__).'/../vendor/autoload.php';

use Ligrila\Payment\Redsys;
use Ligrila\Payment\RedsysConfig;

$json = '{"Ds_SignatureVersion":"HMAC_SHA256_V1","Ds_MerchantParameters":"PUT HERE JSON","Ds_Signature":"SIGNATURE VALUE"}';

$_REQUEST = json_decode($json, true);
$config = new RedsysConfig(
    array(
        'debug' => true,
        'autoRedirect' => false,
        'checkoutLoading' => 'Click on checkout button',
        'checkoutText' => 'Checkout',
        'Ds_Merchant_MerchantCode' => '11111',
        'Ds_Merchant_Terminal' => '2',
        'Ds_Merchant_Password' => 'password',
        'Ds_Merchant_MerchantURL' => $responseUrl,

    )
);
$redsys = new Redsys(
    $config
);

print_r($redsys->parseResponse());
