# Redsys php implementation

BBVA Redsys Payment API implementation with new SHA2 HMAC-256 algorithm


Example
=======
```php

use Ligrila\Payment\Redsys;
use Ligrila\Payment\RedsysConfig;
use Ligrila\Payment\RedsysOrder;

$checkoutUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$responseUrl = dirname($checkoutUrl).'/response.php';
$successUrl = dirname($checkoutUrl).'/success.php';
$errorUrl = dirname($checkoutUrl).'/error.php';

$config = new RedsysConfig(
    array(
        'debug' => true,
        'autoRedirect' => false,
        'checkoutLoading' => 'Click on checkout button',
        'checkoutText' => 'Checkout',
        'Ds_Merchant_MerchantCode' => '111111111',
        'Ds_Merchant_Terminal' => '2',
        'Ds_Merchant_Password' => 'password',
        'Ds_Merchant_MerchantURL' => $responseUrl,
        'Ds_Merchant_UrlOK' => $successUrl,
        'Ds_Merchant_UrlKO' => $errorUrl,

    )
);

$order = new RedsysOrder();
    $order->setAmount(100)
    ->setCurrency('GBP')
    ->setProductDescription('product1');

$redsys = new Redsys($config);

$html = $redsys->checkout($order);

echo $html;
```
