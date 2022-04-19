<?php

/*
 * QR payment for SVK banks accounts
 *
 */

namespace Ontob\QrPayment;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Ontob\QrPayment\Traits\UtilitiesTrait;

class QrPaymentSVK
{
    use UtilitiesTrait;

    /**
     * Bank account number IBAN
     * Required - max 46 characters
     * @var string
     */
    private $iban;

    /**
     * Bank account number SWIFT / BIC
     * @var string
     */
    private $swiftbic;

    /**
     * @var int|string|null
     */
    private $variableSymbol = null;

    /**
     * @var int|string|null
     */
    private $specificSymbol = null;

    /**
     * @var int|string|null
     */
    private $constantSymbol = null;

    /**
     * @var string
     */
    private $currency = 'EUR';

    /**
     * @var string
     */
    private $comment = '';

    /**
     * Payment identifier
     * @var string
     */
    private $internalId = '';

    /**
     * @var DateTimeInterface|null
     */
    private $dueDate = null;

    /**
     * @var float
     */
    private $amount = 0;

    /**
     * @var string
     */
    private $country = 'SK';

    /**
     * @var string
     */
    private $payeeName = '';

    /**
     * @var string
     */
    private $payeeAddressLine1 = '';

    /**
     * @var string
     */
    private $payeeAddressLine2 = '';

    /**
     * @var string
     */
    private $xzBinaryPath;

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
        $this->setDueDate(new \Datetime('now'));
    }

    public static function create($iban = null, $swiftbic = null)
    {
        return new self($iban, $swiftbic);
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

    public function setSwiftbic($swiftbic)
    {
        $this->swiftbic = $swiftbic;
        return $this;
    }

    public function getSwiftbic()
    {
        return $this->swiftbic;
    }

    public function setVariableSymbol($var)
    {
        $this->variableSymbol = $var;
        return $this;
    }

    public function getVariableSymbol()
    {
        return $this->variableSymbol;
    }

    public function setSpecificSymbol($var)
    {
        $this->specificSymbol = $var;
        return $this;
    }

    public function getSpecificSymbol()
    {
        return $this->specificSymbol;
    }

    public function setConstantSymbol($var)
    {
        $this->constantSymbol = $var;
        return $this;
    }

    public function getConstantSymbol()
    {
        return $this->constantSymbol;
    }

    public function setCurrency($var)
    {
        $this->currency = $var;
        return $this;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setComment($var)
    {
        $this->comment = $var;
        return $this;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setInternalId($var)
    {
        $this->internalId = $var;
        return $this;
    }

    public function getInternalId()
    {
        return $this->internalId;
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
        return round($this->amount, 2);
    }

    public function setCountry($var)
    {
        $this->country = $var;
        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setPayeeName($var)
    {
        $this->payeeName = (string) $var;
        return $this;
    }

    public function getPayeeName()
    {
        return $this->payeeName;
    }

    public function setPayeeAddressLine1($var)
    {
        $this->payeeAddressLine1 = (string) $var;
        return $this;
    }

    public function getPayeeAddressLine1()
    {
        return $this->payeeAddressLine1;
    }

    public function setPayeeAddressLine2($var)
    {
        $this->payeeAddressLine2 = (string) $var;
        return $this;
    }

    public function getPayeeAddressLine2()
    {
        return $this->payeeAddressLine2;
    }

    public function setXZbinaryPath($path)
    {
        $this->xzBinaryPath = (string) $path;
        return $this;
    }

    public function getXZbinaryPath()
    {
        if ($this->xzBinaryPath === null) {
            exec('which xz', $output, $return);
            if ($return !== 0) {
                throw new \Exception("'xz' binary not found in PATH, specify it using setXZbinaryPath()");
            }
            if (!isset($output[0])) {
                throw new \Exception("'xz' binary not found in PATH, specify it using setXZbinaryPath()");
            }
            $this->xzBinaryPath = $output[0];
        }
        if (!file_exists($this->xzBinaryPath)) {
            throw new \Exception("The path '{$this->path}' to 'xz' binary is invalid");
        }

        return $this->xzBinaryPath;
    }

    public function validate()
    {
        if ($this->getIban() == null) {
            array_push($this->errors, 'IBAN is required. Cannot be empty.');
        }
        if (!$this->isValidIBAN($this->iban)) {
            array_push($this->errors, 'IBAN is not valid.');
        }
        if ($this->getCurrency() != null && !$this->isValidCurrency($this->getCurrency())) {
            array_push($this->errors, 'Currency code is not valid (ISO 4217 3 char).');
        }
        if ($this->getCountry() != null && !$this->isValidCountryCode($this->getCountry())) {
            array_push($this->errors, 'Country code is not valid (ISO 3166 alpha-3).');
        }
        if (!empty($this->getSwiftbic()) && !$this->isValidBIC($this->getSwiftbic())) {
            array_push($this->errors, 'SWIFT-BIC is not valid.');
        }
        if ($this->getDueDate() != null && strlen($this->getDueDate()) != 8) {
            array_push($this->errors, 'Due date is not valid.');
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
        $array = [
            0 => $this->getInternalId(),
            1 => '1', // payments count
            2 => [
                true, // regular payment
                $this->getAmount(),
                $this->getCurrency(),
                $this->getDueDate(),
                $this->getVariableSymbol(),
                $this->getConstantSymbol(),
                $this->getSpecificSymbol(),
                '', // variable , constant and specific symbol in SEPA format (empty already defined)
                $this->getComment(),
                1, // count of target accounts
                $this->getIban(),
                $this->getSwiftbic(),
                0, // standing order
                0, // direct debit
                $this->getPayeeName(),
                $this->getPayeeAddressLine1(),
                $this->getPayeeAddressLine2(),
            ],
        ];

        $array[2] = implode("\t", $array[2]);

        $data = implode("\t", $array);

        // get the crc32 of the string in binary format and prepend it to the data
        $hashedData = strrev(hash('crc32b', $data, true)) . $data;
        $xzBinary = $this->getXZbinaryPath();

        // we need to get raw lzma1 compressed data with parameters LC=3, LP=0, PB=2, DICT_SIZE=128KiB
        $xzProcess = proc_open("${xzBinary} --format=raw --lzma1=lc=3,lp=0,pb=2,dict=128KiB -c -", [
            0 => [
                'pipe',
                'r',
            ],
            1 => [
                'pipe',
                'w',
            ],
        ], $xzProcessPipes);
        assert(is_resource($xzProcess));

        fwrite($xzProcessPipes[0], $hashedData);
        fclose($xzProcessPipes[0]);

        $pipeOutput = stream_get_contents($xzProcessPipes[1]);
        fclose($xzProcessPipes[1]);
        proc_close($xzProcess);

        // we need to strip the EOF data and prepend 4 bytes of data, first 2 bytes define document type, the other 2
        // define the length of original string, all the magic below does that
        $hashedData = bin2hex("\x00\x00" . pack('v', strlen($hashedData)) . $pipeOutput);

        $base64Data = '';
        for ($i = 0; $i < strlen($hashedData); $i++) {
            $base64Data .= str_pad(base_convert($hashedData[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }

        $length = strlen($base64Data);

        $controlDigit = $length % 5;
        if ($controlDigit > 0) {
            $count = 5 - $controlDigit;
            $base64Data .= str_repeat('0', $count);
            $length += $count;
        }

        $length = $length / 5;
        assert(is_int($length));

        $hashedData = str_repeat('_', $length);

        // convert the resulting binary data (5 bits at a time) according to table from specification
        for ($i = 0; $i < $length; $i++) {
            $hashedData[$i] = '0123456789ABCDEFGHIJKLMNOPQRSTUV'[bindec(substr($base64Data, $i * 5, 5))];
        }

        // and that's it, this totally-not-crazy-overkill, process is done
        return $hashedData;
    }

    public function qrImage()
    {
        $qrCode = new QrCode();
        $qrCode->setText($this->getQrString())
            ->setEncoding('UTF-8')
            // ->setSize(300)
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255]);
        return $qrCode;
    }
}
