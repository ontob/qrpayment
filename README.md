### QR payments code library

Creates QR code for bank in Czechia, Slovakia, Poland and banks using EPC QR code

### Installation

Use [Composer](https://getcomposer.org/) to install the library. Also make sure you have enabled and configured the
[GD extension](https://www.php.net/manual/en/book.image.php) if you want to generate images.
For Slovakia QR codes [xz library ](https://tukaani.org/xz/) is required.

``` bash
composer require ontob/qrpayment
```

### Usage
``` php
use Ontob\QrPayment\QrPaymentCZE;

$qrCode = QrPaymentCZE::create('CZ2920100000002500278163')
                ->setAmount(300)
                ->setCurrency('CZK')
                ->setVariableSymbol('123589123');
```

``` php
use Ontob\QrPayment\QrPaymentSVK;

$qrCode = QrPaymentSVK::create('CZ2920100000002500278163')
                ->setAmount(300)
                ->setCurrency('CZK')
                ->setVariableSymbol('123589123');
                // ->setXZbinaryPath() - optional setting XZ library path if not default
```

``` php
use Ontob\QrPayment\QrPaymentPOL;

$qrCode = QrPaymentPOL::create('CZ2920100000002500278163')
                ->setAmount(300)
                ->setVariableSymbol('123589123');
                // Currency is not used, it is always PL
```
``` php
use Ontob\QrPayment\QrPaymentEPC;

$qrCode = QrPaymentEPC::create('CZ2920100000002500278163')
                ->setAmount(300)
                ->setCurrency('CZK')
                ->setVariableSymbol('123589123');
```

**Check each classes for additional fields.**

<br>

#### Get image
``` php
// returns Endroid\QrCode\Writer\Result\PngResult
$image = $qrCode->qrImage();

// Get Base64 string
$image->getDataUri();
// Save image to file
$image->saveToFile('qrimage.png');
```

<br>

### License
Open-sourced software licensed under the [MIT license](https://github.com/ontob/qrpayment/blob/master/LICENSE.md).
