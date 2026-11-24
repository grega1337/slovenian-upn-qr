# Slovenian UPN QR Generator (ZBS Standard)

[![Latest Stable Version](https://poser.pugxml.org/grsoftware/slovenian-upn-qr/v/stable)](https://packagist.org/packages/grsoftware/slovenian-upn-qr)
[![Total Downloads](https://poser.pugxml.org/grsoftware/slovenian-upn-qr/downloads)](https://packagist.org/packages/grsoftware/slovenian-upn-qr)
[![License](https://poser.pugxml.org/grsoftware/slovenian-upn-qr/license)](https://packagist.org/packages/grsoftware/slovenian-upn-qr)
[![PHP Version Require](https://poser.pugxml.org/grsoftware/slovenian-upn-qr/require/php)](https://packagist.org/packages/grsoftware/slovenian-upn-qr)

A clean, modern, zero-framework PHP 8.2+ library for generating standardized **Slovenian UPN QR payment codes** and **visual UPN payment slips** following the official **ZBS (Združenje bank Slovenije)** banking specification.

> Developed & maintained by [GR Software](https://grsoftware.si).  
> Looking for an automated turnkey solution for WooCommerce? Check out the [GR UPN Payment Form for WooCommerce](https://grsoftware.si/upn-vticnik-woocommerce).

---

## ✨ Features

- 🏦 **100% Compliant** with official ZBS UPN QR standards and checksum calculation.
- 📱 **Mobile Banking Ready**: Scannable by all Slovenian banking apps (NLB Klik, mBank@Net, DH Mobilni, Intesa Sanpaolo, etc.).
- 🖼️ **Multi-Format Output**:
  - Raw UPNQR payload string
  - QR Code PNG binary
  - QR Code SVG vector string
  - Base64 data URI (ready for `<img src="...">`)
  - Complete filled-in visual UPN slip PNG
- 🚀 **Framework Agnostic**: Works effortlessly in Laravel, Symfony, WordPress, Slim, or vanilla PHP.

---

## 📦 Installation

Install via Composer:

```bash
composer require grsoftware/slovenian-upn-qr
```

---

## 🚀 Quick Start

### 1. Generate a Standard UPN QR Code

```php
use GrSoftware\UpnQr\UpnQr;

$upn = UpnQr::create()
    ->setPayer(
        name: 'Janez Novak',
        address: 'Slovenska cesta 1',
        postCity: '1000 Ljubljana'
    )
    ->setReceiver(
        name: 'Moje Podjetje d.o.o.',
        address: 'Dunajska cesta 10',
        postCity: '1000 Ljubljana',
        iban: 'SI56020140015894123'
    )
    ->setAmount(125.50)
    ->setPaymentDetails(
        purpose: 'Plačilo računa 2026-0042',
        reference: 'SI00 20260042',
        code: 'GDSV' // Official purpose code
    );

// Get Base64 image string for direct HTML rendering
$base64Image = $upn->getQrBase64(size: 300);
echo '<img src="' . $base64Image . '" alt="UPN QR Koda" />';

// Or save PNG binary to disk
file_put_contents('upn-qr.png', $upn->getQrPng());

// Or get SVG vector
$svgString = $upn->getQrSvg();
```

---

### 2. Generate a Complete Visual UPN Payment Slip

```php
// Render full UPN slip canvas (including QR code & printed text)
$slipPng = $upn->getSlipPng();
file_put_contents('upn-slip.png', $slipPng);
```

---

### 3. Get Raw Payload String

```php
// Raw string conforming to ZBS specification
$payload = $upn->getPayload();
```

---

## 📋 Requirements

- PHP 8.2 or higher
- `ext-gd`
- `ext-mbstring`

---

## 🛡️ License

This library is open-sourced software licensed under the [MIT license](LICENSE).

## 🏢 Authors & Commercial Support

Maintained by **[GR Software](https://grsoftware.si)** (Grega Rednak s.p.).  
For enterprise integrations, bespoke ERP/CRM connections or custom development, visit [https://grsoftware.si](https://grsoftware.si).
