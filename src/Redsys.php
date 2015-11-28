<?php

namespace Ligrila\Payment;

class Redsys
{
    private $config;
    public function __construct(RedsysConfig $config)
    {
        $this->config = $config;
    }

    public function parseResponse()
    {
        $params = $_REQUEST;
        $ret = array('accepted' => false,'error' => 'Invalid request','amount' => 0);

        if (!empty($params['Ds_MerchantParameters']) && !empty($params['Ds_Signature'])) {
            $api = new RedsysAPI();
            $version = $params['Ds_SignatureVersion'];
            $data = $params['Ds_MerchantParameters'];
            $requestSignature = $params['Ds_Signature'];

            $api->setEncodedParameters($data);

            $s = $api->getReponseSignature($this->config['Ds_Merchant_Password']);

            $code = (int) $api['Ds_Response'];

            if ($s != $requestSignature) {
                $ret = array(
                    'amount' => 0,
                    'accepted' => false,
                    'error' => 'Invalid Ds_Signature',
                );
            } else {
                if ($code <= 99) {
                    $ret = array(
                        'amount' => $api['Ds_Amount'] / 100,
                        'accepted' => true,
                        'error' => false,
                        );
                } else {
                    $ret = array(
                        'amount' => 0,
                        'accepted' => false,
                        'error' => "Unauthorized transaction $code",
                        );
                }
            }
        }

        return $ret;
    }

    public function checkoutParams(RedsysOrder $order)
    {
        /*config*/
        $Ds_Merchant_MerchantCode = $this->config['Ds_Merchant_MerchantCode'];
        $Ds_Merchant_Terminal = $this->config['Ds_Merchant_Terminal'];
        $Ds_Merchant_MerchantURL = $this->config['Ds_Merchant_MerchantURL'];
        $Ds_Merchant_UrlOK = $this->config['Ds_Merchant_UrlOK'];
        $Ds_Merchant_UrlKO = $this->config['Ds_Merchant_UrlKO'];
        $Ds_Merchant_Password = $this->config['Ds_Merchant_Password'];
        $Ds_SignatureVersion = $this->config['Ds_SignatureVersion'];
        $Ds_Merchant_ConsumerLanguage = $this->config['Ds_Merchant_ConsumerLanguage'];
        /*order*/
        $Ds_Merchant_Order = $order->getOrderID();
        $Ds_Merchant_TransactionType = $order->getTransactionType();
        $Ds_Merchant_Currency = $order->getCurrency();
        $Ds_Merchant_Amount = $order->getAmount();
        $Ds_Merchant_ProductDescription = $order->getProductDescription();

        $api = new RedsysAPI();
        $api['Ds_Merchant_Amount'] = $Ds_Merchant_Amount;
        $api['Ds_Merchant_Order'] = $Ds_Merchant_Order;
        $api['Ds_Merchant_MerchantCode'] = $Ds_Merchant_MerchantCode;
        $api['DS_Merchant_Currency'] = $Ds_Merchant_Currency;
        $api['Ds_Merchant_TransactionType'] = $Ds_Merchant_TransactionType;
        $api['Ds_Merchant_Terminal'] = $Ds_Merchant_Terminal;
        $api['Ds_Merchant_MerchantURL'] = $Ds_Merchant_MerchantURL;
        $api['Ds_Merchant_UrlOK'] = $Ds_Merchant_UrlOK;
        $api['Ds_Merchant_UrlKO'] = $Ds_Merchant_UrlKO;
        $api['Ds_Merchant_ProductDescription'] = $Ds_Merchant_ProductDescription;
        $api['Ds_Merchant_ConsumerLanguage'] = $Ds_Merchant_ConsumerLanguage;
    

        $Ds_MerchantParameters = $api->getEncodedParameters();
        $Ds_Signature = $api->getSignature($Ds_Merchant_Password);



        return array(
                    'Ds_MerchantParameters' => $Ds_MerchantParameters,
                    'Ds_Signature' => $Ds_Signature,
                    'Ds_SignatureVersion' => $Ds_SignatureVersion,
                );
    }

    public function checkout(RedsysOrder $order)
    {
        $params = $this->checkoutParams($order);

        $fields = '';
        foreach ($params as $index => $value) {
            $fields .= $this->generateInput($index, $value);
        }

        return $this->generateForm($fields);
    }

    protected function generateInput($name, $value = null, $type = 'hidden')
    {
        $tmp = "<input type=\"$type\" name=\"$name\" value=\"$value\"></input>";

        return $tmp;
    }

    protected function generateForm($fields)
    {
        $form = $this->config['checkoutLoading'].'
                        <form action="'.$this->config->getCheckoutUrl().'" id="redsys_standard_checkout" name="redsys" method="post">
                        '.$fields.'
                        <input type="submit" value="'.$this->config['checkoutText'].'"></input>
                        </form>';
        if ($this->config['autoRedirect']) {
            $form .= '<script type="text/javascript">document.getElementById("redsys_standard_checkout").submit(); </script>';
        }

        return $form;
    }
}
