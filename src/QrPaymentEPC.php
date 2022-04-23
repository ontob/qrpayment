<?php

/*
 * QR payment EPC
 * https://en.wikipedia.org/wiki/EPC_QR_code
 *
 */

namespace Ontob\QrPayment;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Ontob\QrPayment\Traits\UtilitiesTrait;

class QrPaymentEPC
{
    use UtilitiesTrait;

    /**
     * Service Tag
     * Required - max 3 characters
     * @var string
     */
    private $serviceTag = 'BCD';

    /**
     * Version
     * Required - max 3 characters
     * @var string
     */
    private $version = '001';

    /**
     * Character set
     * Required - max 1 characters
     * UTF_8 = 1;
     * ISO8859_1 = 2;
     * ISO8859_2 = 3;
     * ISO8859_4 = 4;
     * ISO8859_5 = 5;
     * ISO8859_7 = 6;
     * ISO8859_10 = 7;
     * ISO8859_15 = 8;
     * @var string
     */
    private $characterSet = '1';

    /**
     * Identification
     * Required - max 3 characters
     * @var string
     */
    private $identification = 'SCT';

    /**
     * Bank account number SWIFT / BIC
     * Required - max 11 characters
     * @var string
     */
    private $swiftbic;

    /**
     * Name of the beneficiary
     * Required - max 70 characters
     * @var string
     */
    private $payeeName;

    /**
     * Bank account number IBAN
     * Required - max 46 characters
     * @var string
     */
    private $iban;

    /**
     * Currency of the payment
     * Max 3 characters
     * @var string
     */
    private $currency = 'EUR';

    /**
     * The amount of the payment
     * Max 12 characters
     * @var float
     */
    private $amount;

    /**
     * Purpose of the payment
     * Max 4 characters
     * @var string
     */
    private $purpose;

    /**
     * Remittance reference
     * Max 35 characters
     * @var string
     */
    private $remittanceReference;

    /**
     * Remittance text
     * Max 140 characters
     * @var string
     */
    private $remittanceText;

    /**
     * Beneficiary to originator information
     * Max 70 characters
     * @var string
     */
    private $information;

    /**
     * Errors array
     * @return array
     */
    private $errors = [];

    public function __construct($iban = null, $swiftbic = null)
    {
        if ($iban != null) {
            $this->setIban($iban);
        }
        if ($swiftbic != null) {
            $this->setSwiftbic($swiftbic);
        }
    }

    public static function create($iban = null, $swiftbic = null)
    {
        return new self($iban, $swiftbic);
    }

    public function setServiceTag($serviceTag)
    {
        $this->serviceTag = $serviceTag;
        return $this;
    }

    public function getServiceTag()
    {
        return $this->serviceTag;
    }

    public function setVersion($version)
    {
        if ($version == 1) {
            $this->version = '001';
        } elseif ($version == 2) {
            $this->version = '002';
        } else {
            $this->version = $version;
        }
        return $this;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setCharacterSet($characterSet)
    {
        $this->characterSet = $characterSet;
        return $this;
    }

    public function getCharacterSet()
    {
        return $this->characterSet;
    }

    public function setIdentification($string)
    {
        $this->identification = $string;
        return $this;
    }

    public function getIdentification()
    {
        return $this->identification;
    }

    public function setSwiftbic($swiftbic)
    {
        $this->swiftbic = $swiftbic;
        return $this;
    }

    public function getSwiftbic()
    {
        return $this->swiftbic;
    }

    public function setPayeeName($name)
    {
        $this->payeeName = $name;
        return $this;
    }

    public function getPayeeName()
    {
        return $this->payeeName;
    }

    public function setIban($iban)
    {
        $this->iban = preg_replace('/\s+/', '', strtoupper($iban));
        return $this;
    }

    public function getIban()
    {
        return $this->iban;
    }

    public function setCurrency($currency)
    {
        $this->currency = (string) $currency;
        return $this;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setAmount($amount)
    {
        $this->amount = (float) $amount;
        return $this;
    }

    public function getAmount()
    {
        return $this->amount > 0 ?
           strtoupper($this->currency) .  number_format($this->amount, 2, '.', '') : '';
    }

    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
        return $this;
    }

    public function getPurpose()
    {
        return $this->purpose;
    }

    public function setRemittanceReference($remittanceReference)
    {
        $this->remittanceReference = $remittanceReference;
        return $this;
    }

    public function getRemittanceReference()
    {
        return $this->remittanceReference;
    }

    public function setRemittanceText($remittanceText)
    {
        $this->remittanceText = $remittanceText;
        return $this;
    }

    public function getRemittanceText()
    {
        return $this->remittanceText;
    }

    public function setInformation($information)
    {
        $this->information = $information;
        return $this;
    }

    public function getInformation()
    {
        return $this->information;
    }

    public function validate()
    {
        if (strlen($this->getServiceTag()) > 3) {
            array_push($this->errors, 'Service tag can be max 3 characters long.');
        }
        if (strlen($this->getVersion()) > 3) {
            array_push($this->errors, 'Version can be max 3 characters long.');
        }
        if (strlen($this->getCharacterSet()) > 1) {
            array_push($this->errors, 'Character set can be max 1 characters long.');
        }
        if (strlen($this->getIdentification()) > 3) {
            array_push($this->errors, 'Identification can be max 3 characters long.');
        }
        if (strlen($this->getPayeeName()) > 70) {
            array_push($this->errors, 'Payee name can be max 70 characters long.');
        }
        if ($this->getCurrency() != null && !$this->isValidCurrency($this->getCurrency())) {
            array_push($this->errors, 'Currency code is not valid.');
        }
        if (strlen($this->getAmount()) > 12) {
            array_push($this->errors, 'Amount can be max 12 characters long.');
        }
        if (strlen($this->getPurpose()) > 4) {
            array_push($this->errors, 'Amount can be max 4 characters long.');
        }
        if (strlen($this->getRemittanceReference()) > 35) {
            array_push($this->errors, 'Remittance reference can be max 35 characters long.');
        }
        if (strlen($this->getRemittanceText()) > 140) {
            array_push($this->errors, 'Remittance text can be max 140 characters long.');
        }
        if (strlen($this->getInformation()) > 70) {
            array_push($this->errors, 'Information can be max 70 characters long.');
        }
        if ($this->getIban() == null) {
            array_push($this->errors, 'IBAN is required.');
        }
        if ($this->getIban() != null && !$this->isValidIBAN($this->iban)) {
            array_push($this->errors, 'IBAN is not valid.');
        }
        if (($this->getVersion() == '001' && empty($this->getSwiftbic())) ||
             $this->getCurrency() != 'EUR' && empty($this->getSwiftbic())) {
            array_push($this->errors, 'SWIFT-BIC is required for version 1 or when currency is not EUR.');
        }
        if (!empty($this->getSwiftbic()) && !$this->isValidBIC($this->getSwiftbic())) {
            array_push($this->errors, 'SWIFT-BIC is not valid.');
        }

        return $this->errors;
    }

    public function isValid()
    {
        $this->validate();
        return empty($this->errors) ? true : false;
    }

    public function getQrString()
    {
        $data = [
            'Service Tag' => $this->getServiceTag() == null ? '' : $this->getServiceTag(),
            'Version' => $this->getVersion() == null ? '' : $this->getVersion(),
            'Character set' => $this->getCharacterSet() == null ? '' : $this->getCharacterSet(),
            'Identification' => $this->getIdentification() == null ? '' : $this->getIdentification(),
            'BIC' => $this->getSwiftbic() == null ? '' : $this->getSwiftbic(),
            'Name' => $this->getPayeeName() == null ? '' : $this->getPayeeName(),
            'IBAN' => $this->getIban() == null ? '' : $this->getIban(),
            'Amount' => $this->getAmount() == null ? '' : $this->getAmount(),
            'Purpose' => $this->getPurpose() == null ? '' : $this->getPurpose(),
            'Remittance (Reference)' => $this->getRemittanceReference() == null ? '' : $this->getRemittanceReference(),
            'Remittance (Text)' => $this->getRemittanceText() == null ? '' : $this->getRemittanceText(),
            'Information' => $this->getInformation() == null ? '' : $this->getInformation(),
        ];

        return rtrim(implode("\n", $data), "\n");
    }

    public function qrImage()
    {
        $qrCode = new QrCode();
        $qrCode->setText($this->getQrString())
            ->setEncoding('UTF-8')
            ->setSize(300)
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::MEDIUM)
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255]);
        return $qrCode;
    }


    public static function formatMoney($currency = 'EUR', $value = 0)
    {
        return sprintf(
            '%s%s',
            strtoupper($currency),
            $value > 0 ? number_format($value, 2, '.', '') : ''
        );
    }
}
