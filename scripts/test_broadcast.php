<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    event(new App\Events\ResourceChanged('product', 'created', ['id' => 'demo-prod', 'name' => 'Demo product']));
    echo "BROADCAST_DISPATCHED\n";
} catch (Throwable $e) {
    echo "BROADCAST_ERROR: " . $e->getMessage() . "\n";
}
