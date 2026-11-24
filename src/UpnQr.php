<?php

declare(strict_types=1);

namespace GrSoftware\UpnQr;

use DateTimeInterface;
use DateTimeImmutable;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use GrSoftware\UpnQr\Exceptions\InvalidUpnException;

/**
 * Slovenian UPN QR Payment Generator
 * Standardized ZBS (Združenje bank Slovenije) specification.
 *
 * @author Grega Rednak <grega.rednak@grsoftware.si>
 * @link https://grsoftware.si/upn-vticnik-woocommerce
 */
class UpnQr
{
    private string $payerName = '';
    private string $payerAddress = '';
    private string $payerPost = '';

    private string $receiverName = '';
    private string $receiverAddress = '';
    private string $receiverPost = '';
    private string $receiverIban = '';
    private string $reference = 'SI00';

    private float $amount = 0.0;
    private string $paymentCode = 'OTHR';
    private string $paymentPurpose = 'Plačilo';
    private DateTimeInterface $dueDate;

    public function __construct()
    {
        $this->dueDate = new DateTimeImmutable('today');
    }

    public static function create(): self
    {
        return new self();
    }

    public function setPayer(string $name, string $address, string $postCity): self
    {
        $this->payerName = trim($name);
        $this->payerAddress = trim($address);
        $this->payerPost = trim($postCity);
        return $this;
    }

    public function setReceiver(string $name, string $address, string $postCity, string $iban): self
    {
        $this->receiverName = trim($name);
        $this->receiverAddress = trim($address);
        $this->receiverPost = trim($postCity);
        $this->receiverIban = strtoupper(preg_replace('/\s+/', '', $iban));
        return $this;
    }

    public function setAmount(float $amount): self
    {
        if ($amount < 0) {
            throw new InvalidUpnException('UPN amount must be greater than or equal to 0.');
        }
        $this->amount = $amount;
        return $this;
    }

    public function setPaymentDetails(string $purpose, string $reference = 'SI00', string $code = 'OTHR', ?DateTimeInterface $dueDate = null): self
    {
        $this->paymentPurpose = trim($purpose);
        $this->reference = trim($reference);
        $this->paymentCode = strtoupper(trim($code));
        if ($dueDate !== null) {
            $this->dueDate = $dueDate;
        }
        return $this;
    }

    public function setReference(string $reference): self
    {
        $this->reference = trim($reference);
        return $this;
    }

    public function setPaymentCode(string $code): self
    {
        $this->paymentCode = strtoupper(trim($code));
        return $this;
    }

    public function setDueDate(DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    /**
     * Build the official standardized ZBS UPNQR payload string.
     */
    public function getPayload(): string
    {
        if (empty($this->receiverIban)) {
            throw new InvalidUpnException('Receiver IBAN is required for UPN QR generation.');
        }

        // Amount in cents formatted to 11 digits
        $cents = (int) round($this->amount * 100);
        $amountCents = sprintf('%011d', $cents);

        $fields = [
            'UPNQR',
            '',
            '    ',
            '',
            '',
            $this->payerName,
            $this->payerAddress,
            $this->payerPost,
            $amountCents,
            '',
            '',
            $this->paymentCode,
            $this->paymentPurpose,
            $this->dueDate->format('d.m.Y'),
            $this->receiverIban,
            $this->reference,
            $this->receiverName,
            $this->receiverAddress,
            $this->receiverPost,
        ];

        // Clean trailing spaces per field
        $cleanFields = array_map(fn($f) => trim($f), $fields);

        // String with newlines
        $payload = implode("\n", $cleanFields) . "\n";

        // Append character count checksum line (standard ZBS requirement)
        $charCount = mb_strlen($payload, 'UTF-8');
        $payload .= $charCount . "\n";

        return $payload;
    }

    /**
     * Generate QR code binary (PNG format)
     */
    public function getQrPng(int $size = 400, int $margin = 0): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            data: $this->getPayload(),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: $margin,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        return $builder->build()->getString();
    }

    /**
     * Generate QR code SVG string
     */
    public function getQrSvg(int $size = 400, int $margin = 0): string
    {
        $builder = new Builder(
            writer: new SvgWriter(),
            data: $this->getPayload(),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: $margin
        );

        return $builder->build()->getString();
    }

    /**
     * Get Base64 data URI of the QR code (ready for <img src="...">)
     */
    public function getQrBase64(int $size = 400): string
    {
        return 'data:image/png;base64,' . base64_encode($this->getQrPng($size));
    }

    /**
     * Generate full printable UPN payment slip PNG with form fields printed.
     */
    public function getSlipPng(): string
    {
        $slip = new UpnSlip($this);
        return $slip->renderPng();
    }

    // Getters for internal/slip rendering
    public function getPayerName(): string { return $this->payerName; }
    public function getPayerAddress(): string { return $this->payerAddress; }
    public function getPayerPost(): string { return $this->payerPost; }
    public function getReceiverName(): string { return $this->receiverName; }
    public function getReceiverAddress(): string { return $this->receiverAddress; }
    public function getReceiverPost(): string { return $this->receiverPost; }
    public function getReceiverIban(): string { return $this->receiverIban; }
    public function getReference(): string { return $this->reference; }
    public function getAmount(): float { return $this->amount; }
    public function getPaymentCode(): string { return $this->paymentCode; }
    public function getPaymentPurpose(): string { return $this->paymentPurpose; }
    public function getDueDate(): DateTimeInterface { return $this->dueDate; }
}
