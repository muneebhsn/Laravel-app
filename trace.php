<?php
echo "Step 1: PHP is alive!\n";
require __DIR__.'/vendor/autoload.php';
echo "Step 2: Composer autoloader loaded successfully.\n";

try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    echo "Step 3: Bootstrap file loaded successfully.\n";
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "Step 4: Console Kernel instantiated.\n";
    
    $kernel->bootstrap();
    echo "Step 5: Application successfully bootstrapped! (The engine is running)\n";
    
} catch (\Throwable $e) {
    echo "\nCRITICAL CRASH DETECTED:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
