<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sale;
use App\Models\Setting;
use App\Services\StatusNotificationService;

$sale = Sale::find(79); // Using the last known ID
if (!$sale) {
    echo "Sale 79 not found. trying latest...\n";
    $sale = Sale::latest()->first();
}

if (!$sale) {
    echo "No sales found.\n";
    exit;
}

echo "Testing notification for Sale ID: {$sale->id} (Ref: {$sale->Ref})\n";

// Manual step-by-step trace
echo "1. Checking client...\n";
$sale->load('client');
if (!$sale->client) {
    echo "FAILED: No client.\n";
    exit;
}
echo "Client: {$sale->client->name}, Email: {$sale->client->email}\n";

echo "2. Checking preferences...\n";
$settings = Setting::first();
$prefs = $settings->notification_preferences;
$event = 'order_placed';
$canEmail = isset($prefs[$event]['email']) ? (bool)$prefs[$event]['email'] : true;
echo "Can email for 'order_placed'? " . ($canEmail ? 'YES' : 'NO') . "\n";
echo "Preferences JSON: " . json_encode($prefs[$event] ?? []) . "\n";

echo "3. Triggering StatusNotificationService::notify...\n";
try {
    StatusNotificationService::notify($event, $sale);
    echo "Notification service call completed (check logs/Mailtrap).\n";
} catch (\Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
