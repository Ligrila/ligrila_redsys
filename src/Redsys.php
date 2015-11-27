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
            $api = new \RedsysAPI();
            $version = $params['Ds_SignatureVersion'];
            $data = $params['Ds_MerchantParameters'];
            $requestSignature = $params['Ds_Signature'];

            $dataDecodec = $api->decodeMerchantParameters($data);

            $s = $api->createMerchantSignatureNotif($this->config->Ds_Merchant_Password, $data);
            $dataDecodec = json_decode($dataDecodec, true);

            $code = (int) $dataDecodec['Ds_Response'];

            if ($s != $requestSignature) {
                $ret = array(
                    'amount' => 0,
                    'accepted' => false,
                    'error' => 'Invalid Ds_Signature',
                );
            } else {
                if ($code <= 99) {
                    $ret = array(
                        'amount' => $dataDecodec['Ds_Amount'] / 100,
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
        $Ds_Merchant_MerchantCode = $this->config->Ds_Merchant_MerchantCode;
        $Ds_Merchant_Terminal = $this->config->Ds_Merchant_Terminal;
        $Ds_Merchant_MerchantURL = $this->config->Ds_Merchant_MerchantURL;
        $Ds_Merchant_UrlOK = $this->config->Ds_Merchant_UrlOK;
        $Ds_Merchant_UrlKO = $this->config->Ds_Merchant_UrlKO;
        $Ds_Merchant_Password = $this->config->Ds_Merchant_Password;
        $Ds_SignatureVersion = $this->config->Ds_SignatureVersion;
        $Ds_Merchant_ConsumerLanguage = $this->config->Ds_Merchant_ConsumerLanguage;
        /*order*/
        $Ds_Merchant_Order = $order->getOrderID();
        $Ds_Merchant_TransactionType = $order->getTransactionType();
        $Ds_Merchant_Currency = $order->getCurrency();
        $Ds_Merchant_Amount = $order->getAmount();
        $Ds_Merchant_ProductDescription = $order->getProductDescription();

        $api = new \RedsysAPI();
        $api->setParameter('DS_MERCHANT_AMOUNT', (string) $Ds_Merchant_Amount);
        $api->setParameter('DS_MERCHANT_ORDER', $Ds_Merchant_Order);
        $api->setParameter('DS_MERCHANT_MERCHANTCODE', $Ds_Merchant_MerchantCode);
        $api->setParameter('DS_MERCHANT_CURRENCY', $Ds_Merchant_Currency);
        $api->setParameter('DS_MERCHANT_TRANSACTIONTYPE', $Ds_Merchant_TransactionType);
        $api->setParameter('DS_MERCHANT_TERMINAL', $Ds_Merchant_Terminal);
        $api->setParameter('DS_MERCHANT_MERCHANTURL', $Ds_Merchant_MerchantURL);
        $api->setParameter('DS_MERCHANT_URLOK', $Ds_Merchant_UrlOK);
        $api->setParameter('DS_MERCHANT_URLKO', $Ds_Merchant_UrlKO);
        $api->setParameter('DS_MERCHANT_PRODUCTDESCRIPTION', $Ds_Merchant_ProductDescription);
        $api->setParameter('DS_MERCHANT_CONSUMERLANGUAGE', $Ds_Merchant_ConsumerLanguage);

        $Ds_MerchantParameters = $api->createMerchantParameters();
        $Ds_Signature = $api->createMerchantSignature($Ds_Merchant_Password);
        $Ds_SignatureVersion = 'HMAC_SHA256_V1';

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
        $form = $this->config->checkoutLoading.'
                        <form action="'.$this->config->getCheckoutUrl().'" id="redsys_standard_checkout" name="redsys" method="post">
                        '.$fields.'
                        <input type="submit" value="'.$this->config->checkoutText.'"></input>
                        </form>';
        if ($this->config->autoRedirect) {
            $form .= '<script type="text/javascript">document.getElementById("redsys_standard_checkout").submit(); </script>';
        }

        return $form;
    }
}
