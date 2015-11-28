<?php

namespace Ligrila\Payment;

class RedsysConfig implements \ArrayAccess
{
    private $values = array(
        'Ds_Merchant_MerchantCode'=>null,
        'Ds_Merchant_Terminal'=>null,
        'Ds_Merchant_Password'=>null,
        'Ds_Merchant_MerchantURL'=>null,
        'Ds_Merchant_UrlOK'=>null,
        'Ds_Merchant_UrlKO'=>null,
        'Ds_SignatureVersion'=>'HMAC_SHA256_V1',
        'Ds_Merchant_ConsumerLanguage'=>'001',
        'debug'=>false,
        'autoRedirect'=>true,
        'checkoutText'=>'Checkout',
        'checkoutLoading'=>'Loading...',
        'checkoutUrl'=>'https://sis.redsys.es/sis/realizarPago',
        'debugUrl'=>'https://sis-t.redsys.es:25443/sis/realizarPago'
    );
    
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->values[] = (string)$value;
        } else {
            $this->values[$offset] = (string)$value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->values[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->values[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->values[$offset]) ? $this->values[$offset] : null;
    }
    

    public function __construct(array  $options = array())
    {
        foreach ($options as $k => $v) {
            $this->values[$k] = $v;
        }
    }

    public function getCheckoutUrl()
    {
        return $this['debug'] ? $this['debugUrl'] : $this['checkoutUrl'];
    }
}
