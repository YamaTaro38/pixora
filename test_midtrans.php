<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$midtrans = new App\Services\MidtransService();
$booking = App\Models\Booking::first();
if (!$booking) {
    echo "No booking found";
    exit;
}
try {
    echo $midtrans->createSnapToken($booking, 10000);
} catch (Exception $e) {
    echo $e->getMessage();
}
