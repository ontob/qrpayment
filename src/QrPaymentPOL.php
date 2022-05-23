<?php

/*
 * QR payment for POL banks accounts
 * https://zbp.pl/public/repozytorium/dla_bankow/rady_i_komitety/bankowosc_elektroczniczna/rada_bankowosc_elektr/zadania/2013.12.03_-_Rekomendacja_-_Standard_2D.pdf
 *
 */

namespace Ontob\QrPayment;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Ontob\QrPayment\Traits\UtilitiesTrait;

class QrPaymentPOL
{
    use UtilitiesTrait;

    public const DELIMITER = '|';

    /**
     * NIP number (Tax Identification number) in Poland
     * Max. 10 characters
     * @var string
     */
    private $nip;

    /**
     * Bank account number IBAN
     * Required
     * @var string
     */
    private $iban;

    /**
     * Account number - IBAN without country
     * length 26 characters
     * @var string
     */
    private $accountNumber;

    /**
     * Country code ISO 3166-1 alpha-2, max 2 characters
     * Supports only Poland
     * @var string
     */
    private $country = 'PL';

    /**
     * Min. 6 characters
     * @var string|int|float
     */
    private $amount = '000000';

    /**
     * Recipient's name
     * Max. 20 characters
     * @var string
     */
    private $payeeName;

    /**
     * Payment title
     * Max. 32 characters
     * @var string
     */
    private $paymentTitle;

    /**
     * Direct debit ID
     * Max. 20 characters
     * @var string
     */
    private $directDebitId;

    /**
     * Invobill ID
     * Max. 12 characters
     * @var string
     */
    private $invobillId;

    /**
     * Reserved
     * Max. 24 characters
     * @var string
     */
    private $reserved;

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
    }

    public static function create($iban = null)
    {
        return $iban == null ? new self() : new self($iban);
    }

    public function setNip($nip)
    {
        $this->nip = $nip;
        return $this;
    }

    public function getNip()
    {
        return $this->nip;
    }

    public function setIban($iban)
    {
        $this->iban = preg_replace('/\s+/', '', strtoupper($iban));
        $this->accountNumber = substr($this->iban, 2);
        return $this;
    }

    public function getIban()
    {
        return $this->iban;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    public function setAmount($amount)
    {
        $amount = round((float) $amount, 2) * 100;
        $amount = number_format($amount, 0, '.', '');
        $this->amount = str_pad($amount, 6, "0", STR_PAD_LEFT);
        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setPayeeName($string)
    {
        $this->payeeName = trim((string) $string);
        return $this;
    }

    public function getPayeeName()
    {
        return $this->payeeName;
    }

    public function setPaymentTitle($string)
    {
        $this->paymentTitle = trim((string) $string);
        return $this;
    }

    public function getPaymentTitle()
    {
        return $this->paymentTitle;
    }

    public function setVariableSymbol($string)
    {
        $this->directDebitId = trim((string) $string);
        return $this;
    }

    public function setDirectDebitId($string)
    {
        $this->directDebitId = trim((string) $string);
        return $this;
    }

    public function getDirectDebitId()
    {
        return $this->directDebitId;
    }

    public function setInvobillId($string)
    {
        $this->invobillId = trim((string) $string);
        return $this;
    }

    public function getInvobillId()
    {
        return $this->invobillId;
    }

    public function setReserved($string)
    {
        $this->reserved = trim((string) $string);
        return $this;
    }

    public function getReserved()
    {
        return $this->reserved;
    }

    public function validate()
    {
        if ($this->getNip() != null && strlen($this->getNip()) != 10) {
            array_push($this->errors, 'NIP must have 10 characters.');
        }
        if ($this->getIban() == null) {
            array_push($this->errors, 'IBAN is required. Cannot be empty.');
        }
        if (!$this->isValidIBAN($this->iban)) {
            array_push($this->errors, 'IBAN is not valid.');
        }
        if ($this->getCountry() != null && strlen($this->getCountry()) != 2) {
            array_push($this->errors, 'Country code must have 2 characters.');
        }
        if (strlen($this->getAmount()) < 6) {
            array_push($this->errors, 'Amount has to be min 6 characters long.');
        }
        if ($this->getPayeeName() != null && strlen($this->getPayeeName()) > 20) {
            array_push($this->errors, 'Payee name can be max 20 characters long.');
        }
        if ($this->getPaymentTitle() != null && strlen($this->getPaymentTitle()) > 32) {
            array_push($this->errors, 'Payment title can be max 32 characters long.');
        }
        if ($this->getDirectDebitId() != null && strlen($this->getDirectDebitId()) > 20) {
            array_push($this->errors, 'Direct debit ID can be max 20 characters long.');
        }
        if ($this->getInvobillId() != null && strlen($this->getInvobillId()) > 12) {
            array_push($this->errors, 'Invoobill ID can be max 12 characters long.');
        }
        if ($this->getReserved() != null && strlen($this->getReserved()) > 24) {
            array_push($this->errors, 'Reserved field can be max 24 characters long.');
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
        $qr = $this->getNip() . self::DELIMITER;
        $qr = $qr . $this->getCountry() . self::DELIMITER;
        $qr = $qr . $this->getAccountNumber() . self::DELIMITER;
        if ($this->getAmount() == null) {
            $qr = $qr . '000000' . self::DELIMITER; // manual entry of the amount by user
        } else {
            $qr = $qr . $this->getAmount() . self::DELIMITER;
        }
        $qr = $qr . $this->getPayeeName() . self::DELIMITER;
        $qr = $qr . $this->getPaymentTitle() . self::DELIMITER;
        $qr = $qr . $this->getDirectDebitId() . self::DELIMITER;
        $qr = $qr . $this->getInvobillId() . self::DELIMITER;
        $qr = $qr . $this->getReserved();

        return $qr;
    }

    public function qrImage()
    {
        $writer = new PngWriter();
        $qrCode = QrCode::create($this->getQrString())
            ->setEncoding(new Encoding('UTF-8'))
            ->setSize(300)
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelMedium)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        return $result = $writer->write($qrCode);
    }
}
