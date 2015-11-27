<?php

namespace Ligrila\Payment;

class RedsysConfig
{
    public $Ds_Merchant_MerchantCode;
    public $Ds_Merchant_Terminal;
    public $Ds_Merchant_Password;
    public $Ds_Merchant_MerchantURL;
    public $Ds_Merchant_UrlOK;
    public $Ds_Merchant_UrlKO;
    public $Ds_SignatureVersion = 'HMAC_SHA256_V1';
    public $Ds_Merchant_ConsumerLanguage = '001';

    public $debug = false;
    public $autoRedirect = true;
    public $checkoutText = 'Checkout';
    public $checkoutLoading = 'Loading...';

    public $checkoutUrl = 'https://sis.redsys.es/sis/realizarPago';
    public $debugUrl = 'https://sis-t.redsys.es:25443/sis/realizarPago';

    public function __construct(array  $options = array())
    {
        foreach ($options as $att => $v) {
            $this->{$att} = $v;
        }
    }

    public function getCheckoutUrl()
    {
        return $this->debug ? $this->debugUrl : $this->checkoutUrl;
    }
}
