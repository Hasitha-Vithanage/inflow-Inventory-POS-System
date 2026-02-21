<?php

use App\Models\ShippingCompany;
use App\Models\ShippingMethod;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$log = "";
$log .= "--- Shipping Companies ---\n";
$companies = ShippingCompany::all();
if ($companies->isEmpty()) {
    $log .= "No companies found.\n";
} else {
    foreach ($companies as $company) {
        $log .= "ID: {$company->id}, Name: {$company->name}, Deleted At: " . ($company->deleted_at ?? 'NULL') . "\n";
    }
}

$log .= "\n--- Shipping Methods ---\n";
$methods = ShippingMethod::all();
if ($methods->isEmpty()) {
    $log .= "No methods found.\n";
} else {
    foreach ($methods as $method) {
        $log .= "ID: {$method->id}, Name: {$method->name}, Deleted At: " . ($method->deleted_at ?? 'NULL') . "\n";
    }
}


$log .= "\n--- Permissions Check ---\n";
$p_method = \App\Models\Permission::where('name', 'shipping_methods')->first();
$log .= "shipping_methods permission: " . ($p_method ? "Found (ID: {$p_method->id})" : "MISSING") . "\n";

$p_company = \App\Models\Permission::where('name', 'shipping_companies')->first();
$log .= "shipping_companies permission: " . ($p_company ? "Found (ID: {$p_company->id})" : "MISSING") . "\n";


$log .= "\n--- Policy Simulation ---\n";
try {
    // get first user
    $user = \App\Models\User::first();
    if (!$user) {
        $log .= "No user found to test.\n";
    } else {
        $log .= "Testing with User ID: {$user->id}\n";
        $permission = \App\Models\Permission::where('name', 'shipping_methods')->first();
        if ($permission) {
            $roles = $permission->roles; // Trigger relation load
            $log .= "Permission Roles Count: " . $roles->count() . "\n";
            try {
                $hasRole = $user->hasRole($roles);
                $log .= "User hasRole result: " . ($hasRole ? 'TRUE' : 'FALSE') . "\n";
            } catch (\Exception $e) {
                 $log .= "User->hasRole CRASHED: " . $e->getMessage() . "\n";
                 $log .= $e->getTraceAsString() . "\n";
            }
        } else {
            $log .= "Permission shipping_methods NOT FOUND.\n";
        }
    }
} catch (\Exception $e) {
    $log .= "Policy Simulation CRASHED: " . $e->getMessage() . "\n";
    $log .= $e->getTraceAsString() . "\n";
}

file_put_contents('shipping_debug.log', $log);
echo "Done writing to shipping_debug.log\n";

