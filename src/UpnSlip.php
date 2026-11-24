<?php

declare(strict_types=1);

namespace GrSoftware\UpnQr;

use GrSoftware\UpnQr\Exceptions\InvalidUpnException;

/**
 * Visual UPN Payment Slip Canvas Generator (GD-based).
 */
class UpnSlip
{
    private UpnQr $upn;
    private const FONT = __DIR__ . '/../assets/fonts/courier-new-bold.ttf';
    private const TEMPLATE = __DIR__ . '/../assets/media/upn.png';

    private const FONT_SIZE = 17;
    private const FONT_SMALL = 12;

    public function __construct(UpnQr $upn)
    {
        $this->upn = $upn;
    }

    public function renderPng(): string
    {
        if (!extension_loaded('gd')) {
            throw new InvalidUpnException('The PHP GD extension is required to render visual UPN slips.');
        }

        if (!file_exists(self::TEMPLATE)) {
            throw new InvalidUpnException('UPN template image not found: ' . self::TEMPLATE);
        }

        $image = imagecreatefrompng(self::TEMPLATE);
        if (!$image) {
            throw new InvalidUpnException('Failed to load UPN template image.');
        }

        $color = imagecolorallocate($image, 0x00, 0x00, 0x00);

        $write = function (int $x, int $y, string $text, int $size = self::FONT_SIZE) use ($image, $color) {
            if ($text === '') {
                return;
            }
            if (file_exists(self::FONT)) {
                imagettftext($image, $size, 0, $x, $y, $color, self::FONT, $text);
            } else {
                imagestring($image, 4, $x, $y, $text, $color);
            }
        };

        // Payer (Right / Main)
        $write(697, 170, $this->upn->getPayerName());
        $write(697, 201, $this->upn->getPayerAddress());
        $write(697, 233, $this->upn->getPayerPost());

        // Payer (Left / Talon)
        $write(30, 62, $this->upn->getPayerName(), self::FONT_SMALL);
        $write(30, 87, $this->upn->getPayerAddress(), self::FONT_SMALL);
        $write(30, 112, $this->upn->getPayerPost(), self::FONT_SMALL);

        // Receiver (Right / Main)
        $write(418, 507, $this->upn->getReceiverName());
        $write(418, 538, $this->upn->getReceiverAddress());
        $write(418, 570, $this->upn->getReceiverPost());

        // Receiver (Left / Talon)
        $write(30, 405, $this->upn->getReceiverName(), self::FONT_SMALL);
        $write(30, 430, $this->upn->getReceiverAddress(), self::FONT_SMALL);
        $write(30, 455, $this->upn->getReceiverPost(), self::FONT_SMALL);

        // IBAN (Formatted)
        $formattedIban = trim(chunk_split($this->upn->getReceiverIban(), 4, ' '));
        $write(418, 400, $formattedIban);
        $write(30, 300, $formattedIban, self::FONT_SMALL);

        // Reference
        $refParts = explode(' ', $this->upn->getReference(), 2);
        $refModel = $refParts[0] ?? 'SI00';
        $refNum = $refParts[1] ?? '';
        $write(418, 451, $refModel);
        $write(528, 451, $refNum);
        $write(30, 351, $this->upn->getReference(), self::FONT_SMALL);

        // Purpose & Code
        $write(418, 340, $this->upn->getPaymentCode());
        $write(528, 340, $this->upn->getPaymentPurpose());
        $write(30, 165, $this->upn->getPaymentPurpose(), 10);

        // Due Date
        $dueDateStr = $this->upn->getDueDate()->format('d.m.Y');
        $write(1155, 340, $dueDateStr);
        $write(30, 195, $dueDateStr, self::FONT_SMALL);

        // Amount
        $formattedAmount = '***' . number_format($this->upn->getAmount(), 2, ',', '');
        $write(750, 285, $formattedAmount);
        $write(110, 247, $formattedAmount, self::FONT_SMALL);

        // Overlay QR Code
        $qrPng = $this->upn->getQrPng(400, 0);
        $qrRes = imagecreatefromstring($qrPng);
        if ($qrRes) {
            imagecopyresampled($image, $qrRes, 433, 60, 0, 0, 220, 220, imagesx($qrRes), imagesy($qrRes));
            imagedestroy($qrRes);
        }

        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        return (string) $binary;
    }
}
