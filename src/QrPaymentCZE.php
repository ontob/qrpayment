<?php

/*
 * QR payment for CZE banks accounts
 * https://qr-platba.cz/pro-vyvojare/specifikace-formatu/
 *
 * The string can contain any characters from the ISO-8859-1 character set.
 * For efficient storage in the QR code, we recommend compiling the string so that it contains only the following characters:
 * 0-9
 * AZ [capital letters only]
 * space
 * $, %, *, +, -, ., /, :
 *
 */

namespace Ontob\QrPayment;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Ontob\QrPayment\Traits\UtilitiesTrait;

class QrPaymentCZE
{
    use UtilitiesTrait;

    /**
     * Verze QR formátu QR Platby.
     */
    private const VERSION = '1.0';

    /**
     * Bank account number IBAN
     * Required - max 46 characters
     * @var string
     */
    private $iban;

    /**
     * The amount of the payment
     * Max. 10 characters - max. 2 decimal digits. Dot as a decimal separator.
     * @var float|string
     */
    private $amount;

    /**
     * Payment currency
     * ISO 4217 - length 3 characters, capital letters
     * @var string
     */
    private $currency;

    /**
     * Due date
     * Length 8 characters - YYYYMMDD
     * @var string|Date
     */
    private $dueDate;

    /**
     * Message for recipient
     * Max. 60 characters, all characters in the allowed set except '*'
     * @var string
     */
    private $message;

    /**
     * Variable symbol
     * Max. 10 characters
     * @var int|string
     */
    private $variableSymbol;

    /**
     * Specific symbol
     * Max. 10 characters
     * @var int|string
     */
    private $specificSymbol;

    /**
     * Constant symbol
     * Max. 10 characters
     * @var int|string
     */
    private $constantSymbol;

    /**
     * Payee ID
     * Max. 16 characters
     * @var int|string
     */
    private $payeeId;

    /**
     * Recipient's name
     * Max. 35 characters
     * @var string
     */
    private $payeeName;

    /**
     * Type of payment - IP for immediate payment
     * Length 3 characters
     * @var string
     */
    private $pt;
    private $immediatePayment = false;

    /**
     * Channel for sending the notification to the payment issuer
     * P - notification will be sent by SMS
     * E - notification will be sent by e-mail
     * Length 1 character
     * @var string
     */
    private $nt;

    /**
     * International or local telephone number or e-mail address
     * Max. 320 characters
     * @var string
     */
    private $nta;

    /**
     * The number of days after which an unsuccessful payment attempt should be made again
     * Max. 2 characters
     * @var int
     */
    private $per;

    /**
     * Payer-side payment identifier.
     * This is an internal ID, the use and interpretation of which depends on the payer's bank.
     * Max. 20 characters
     * @var int|string
     */
    private $id;

    /**
     * A URL that can be used for your own use
     * Max. 140 characters
     * @var string
     */
    private $url;

    /**
     * Checksum HEX
     * Length 8 characters
     * @var string
     */
    private $crc32 = null;

    /**
     * Errors array
     * @return array
     */
    private $errors = [];

    public function __construct($iban = null)
    {
        if ($iban != null) {
            $this->setIban($iban);
        }
        $this->setDueDate(new \Datetime('now'));
    }

    public static function create($iban = null)
    {
        return $iban == null ? new self() : new self($iban);
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

    public function setAmount($amount)
    {
        if (is_string($amount)) {
            $amount = str_replace(',', '.', $amount);
            $amount = preg_replace('/\s+/', '', $amount);
        }
        $this->amount = (float) $amount;
        return $this;
    }

    public function getAmount()
    {
        return number_format($this->amount, 2, '.', '');
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setDueDate($date)
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        $this->dueDate = $date;
        return $this;
    }

    public function getDueDate()
    {
        return $this->dueDate != null ? $this->dueDate->format('Ymd') : null;
    }

    public function setMessage($string)
    {
        $this->message = str_replace('*', '', $this->stripDiacritics((string) $string));
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setVariableSymbol($string)
    {
        $this->variableSymbol = (int) $string;
        return $this;
    }

    public function getVariableSymbol()
    {
        return $this->variableSymbol;
    }

    public function setSpecificSymbol($string)
    {
        $this->specificSymbol = (int) $string;
        return $this;
    }

    public function getSpecificSymbol()
    {
        return $this->specificSymbol;
    }

    public function setConstantSymbol($string)
    {
        $this->constantSymbol = (int) $string;
        return $this;
    }

    public function getConstantSymbol()
    {
        return $this->constantSymbol;
    }

    public function setPayeeId($string)
    {
        $this->payeeId = (int) $string;
        return $this;
    }

    public function getPayeeId()
    {
        return $this->payeeId;
    }

    public function setPayeeName($string)
    {
        $this->payeeName = str_replace('*', '', $this->stripDiacritics((string) $string));
        return $this;
    }

    public function getPayeeName()
    {
        return $this->payeeName;
    }

    public function setPt($string)
    {
        $this->pt = str_replace('*', '', $this->stripDiacritics((string) $string));
        return $this;
    }

    public function getPt()
    {
        return $this->pt;
    }

    public function immediatePayment($val)
    {
        $this->immediatePayment = (bool) $val;
        (bool) $val ? $this->pt = 'IP' : $this->pt = null;
        return $this;
    }

    public function setNotification($channel, $contact)
    {
        $channel = strtoupper($channel);
        if ($channel == 'E' || $channel == 'EMAIL') {
            $this->nt = 'E';
            $this->nta = $contact;
        } elseif ($channel == 'P' || $channel == 'PHONE') {
            $this->nt = 'P';
            $this->nta = $contact;
        } else {
            $this->nt = null;
            $this->nta = null;
        }
        return $this;
    }

    public function getNotification()
    {
        return $this->nt;
    }

    public function getContact()
    {
        return $this->nta;
    }

    public function setRetry($int)
    {
        $this->per = (int) $int;
        return $this;
    }

    public function getRetry()
    {
        return $this->per;
    }

    public function setPayerSideId($val)
    {
        $this->id = (string) $val;
        return $this;
    }

    public function getPayerSideId()
    {
        return $this->id;
    }

    public function setUrl($string)
    {
        $this->url = filter_var($string, FILTER_SANITIZE_URL);
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function validate()
    {
        if ($this->getIban() == null) {
            array_push($this->errors, 'IBAN is required. Cannot be empty.');
        }
        if (!$this->isValidIBAN($this->iban)) {
            array_push($this->errors, 'IBAN is not valid.');
        }
        if (strlen($this->getAmount()) > 10) {
            array_push($this->errors, 'Amount can be max 10 characters long.');
        }
        if ($this->getCurrency() != null && !$this->isValidCurrency($this->getCurrency())) {
            array_push($this->errors, 'Currency code is not valid.');
        }
        if ($this->getDueDate() != null && strlen($this->getDueDate()) != 8) {
            array_push($this->errors, 'Due date is not valid.');
        }
        if (strlen($this->getMessage()) > 60) {
            array_push($this->errors, 'Message can be max 60 characters long.');
        }
        if (strlen($this->getVariableSymbol()) > 10 && is_int($this->getVariableSymbol())) {
            array_push($this->errors, 'Variable symbol has to be integer max 10 numbers long.');
        }
        if (strlen($this->getSpecificSymbol()) > 10 && is_int($this->getSpecificSymbol())) {
            array_push($this->errors, 'Specific symbol has to be integer max 10 numbers long.');
        }
        if (strlen($this->getConstantSymbol()) > 10 && is_int($this->getConstantSymbol())) {
            array_push($this->errors, 'Constant symbol has to be integer max 10 numbers long.');
        }
        if (strlen($this->getPayeeId()) > 16 && is_int($this->getPayeeId())) {
            array_push($this->errors, 'Payee ID has to be integer max 16 numbers long.');
        }
        if (strlen($this->getPayeeName()) > 35) {
            array_push($this->errors, 'Payee Name has to be max 35 characters long.');
        }
        if (strlen($this->getPayeeName()) > 35) {
            array_push($this->errors, 'Payee Name has to be max 35 characters long.');
        }
        if (strlen($this->getPt()) > 3) {
            array_push($this->errors, 'Payment type can be max 3 characters long.');
        }
        // TODO phone and email validation
        if ($this->getRetry() != null && (!is_int($this->getRetry()) || ($this->getRetry() < 0 && $this->getRetry() > 30))) {
            array_push($this->errors, 'Retry days should be integer from 0 to 30');
        }
        if (strlen($this->getPayerSideId()) > 20) {
            array_push($this->errors, 'Payer-side id can be max 20 characters long.');
        }
        if ($this->getUrl() != null && (!$this->isValidUrl($this->getUrl()) || strlen($this->getUrl()) > 140)) {
            array_push($this->errors, 'Url must have scheme and host, can be max 140 characters long.');
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
        $qr = "SPD*" . self::VERSION;

        if ($this->getIban() == null || !$this->isValidIBAN($this->iban)) {
            return $qr;
        } else {
            $qr = $qr . "*ACC:" . $this->getIban();
        }

        if ($this->getAmount() != null) {
            $qr = $qr . "*AM:" . $this->getAmount();
        }
        if ($this->getCurrency() != null) {
            $qr = $qr . "*CC:" . $this->getCurrency();
        }
        if ($this->getDueDate() != null) {
            $qr = $qr . "*DT:" . $this->getDueDate();
        }
        if ($this->getMessage() != null) {
            $qr = $qr . "*MSG:" . $this->getMessage();
        }
        if ($this->getVariableSymbol() != null) {
            $qr = $qr . "*X-VS:" . $this->getVariableSymbol();
        }
        if ($this->getSpecificSymbol() != null) {
            $qr = $qr . "*X-SS:" . $this->getSpecificSymbol();
        }
        if ($this->getConstantSymbol() != null) {
            $qr = $qr . "*X-KS:" . $this->getConstantSymbol();
        }
        if ($this->getPayeeId() != null) {
            $qr = $qr . "*RF:" . $this->getPayeeId();
        }
        if ($this->getPayeeName() != null) {
            $qr = $qr . "*RN:" . $this->getPayeeName();
        }
        if ($this->getPt() != null) {
            $qr = $qr . "*PT:" . $this->getPt();
        }
        if ($this->getNotification() != null) {
            $qr = $qr . "*NT:" . $this->getNotification();
        }
        if ($this->getContact() != null) {
            $qr = $qr . "*NTA:" . $this->getContact();
        }
        if ($this->getRetry() != null) {
            $qr = $qr . "*X-PER:" . $this->getRetry();
        }
        if ($this->getPayerSideId() != null) {
            $qr = $qr . "*X-ID:" . $this->getPayerSideId();
        }
        if ($this->getUrl() != null) {
            $qr = $qr . "*X-URL:" . $this->getUrl();
        }

        return $qr;
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
}
