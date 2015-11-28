<?php
namespace Ligrila\Payment;

class RedsysAPI implements \ArrayAccess 
{
    private $parameters = array();

    // array_access

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->parameters[] = (string)$value;
        } else {
            $this->parameters[$offset] = (string)$value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->parameters[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->parameters[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->parameters[$offset]) ? $this->parameters[$offset] : null;
    }
    
    
    // checkout
    
    public function getEncodedParameters(){
        return base64_encode(json_encode($this->parameters));
    }
    
    public function getKey($password,$order){
            $bytes = array(0,0,0,0,0,0,0,0);
            $iv = implode(array_map("chr", $bytes));
            $key = mcrypt_encrypt(MCRYPT_3DES, $password, $order, MCRYPT_MODE_CBC, $iv);
            return $key;
    }
    
    public function getSignature($password){
        $password = base64_decode($password);
        $params = $this->getEncodedParameters();
        $order = !empty($this['Ds_Merchant_Order']) ? $this['Ds_Merchant_Order'] : $this['Ds_Order'];
        $key = $this->getKey($password,$order);
        $signature =  base64_encode(hash_hmac('sha256', $params, $key, true));
        
        return $signature;
    }
    
    
    //response
    
    public function setEncodedParameters($params){
        $decoded =  json_decode(base64_decode($params),true);
        $this->parameters += $decoded;
        return $this;
    }
    
    public function getReponseSignature($password){
        $signature = $this->getSignature($password);
        return strtr($signature, '+/', '-_');
    }
    
    
    

}