<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    event(new App\Events\ProductStockUpdated('demo-prod','demo-branch',3));
    echo "EVENT_DISPATCHED\n";

    $pb = App\Models\ProductBranch::where('product_id','demo-prod')->where('branch_id','demo-branch')->first();
    $log = App\Models\LogEntry::where('auditable_id','demo-prod')->where('action','notification.low_stock')->first();

    echo json_encode(['product_branch' => $pb ? $pb->toArray() : null, 'log' => $log ? $log->toArray() : null]);
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}
