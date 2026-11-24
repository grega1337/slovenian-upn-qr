<?php

require_once __DIR__ . '/vendor/autoload.php';

// Manual autoload for the library classes during development
spl_autoload_register(function ($class) {
    $prefix = 'GrSoftware\\UpnQr\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use GrSoftware\UpnQr\UpnQr;

$upn = UpnQr::create()
    ->setPayer('Janez Novak', 'Slovenska 1', '1000 Ljubljana')
    ->setReceiver('Moje Podjetje d.o.o.', 'Dunajska 10', '1000 Ljubljana', 'SI56020140015894123')
    ->setAmount(125.50)
    ->setPaymentDetails('Naročilo #1024', 'SI00 1024', 'OTHR');

$payload = $upn->getPayload();
echo "--- UPNQR PAYLOAD ---\n";
echo $payload;
echo "---------------------\n";
echo "Character length verified: " . strlen($payload) . " bytes\n";
