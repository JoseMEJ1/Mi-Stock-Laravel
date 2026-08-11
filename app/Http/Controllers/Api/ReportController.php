<?php

namespace App\Http\Controllers\Api;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\ProductBranch;
use App\Models\LogEntry;
use App\Models\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends ApiController
{
    public function salesSummary(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $query = Sale::query();
        $tenantId = $this->tenantIdFromUser($user);
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->where('tenant_id', '__no_tenant__');
        }
        if ($request->filled('from')) {
            $query->where('sold_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('sold_at', '<=', $request->input('to'));
        }

        $sales = $query->get();
        $summary = [
            'count' => $sales->count(),
            'total' => $sales->sum('total'),
            'by_status' => $sales->groupBy('status')->map->sum('total'),
        ];

        return $this->success($summary, 'Sales summary generated.');
    }

    public function purchaseSummary(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $query = Purchase::query();
        $tenantId = $this->tenantIdFromUser($user);
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->where('tenant_id', '__no_tenant__');
        }
        if ($request->filled('from')) {
            $query->where('purchased_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('purchased_at', '<=', $request->input('to'));
        }

        $purchases = $query->get();
        $summary = [
            'count' => $purchases->count(),
            'total' => $purchases->sum('total'),
            'by_status' => $purchases->groupBy('status')->map->sum('total'),
        ];

        return $this->success($summary, 'Purchase summary generated.');
    }

    public function inventorySummary(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $query = ProductBranch::query();
        $tenantId = $this->tenantIdFromUser($user);
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->where('tenant_id', '__no_tenant__');
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $inventory = $query->get()->map(function ($stock) {
            return [
                'product_id' => $stock->product_id,
                'branch_id' => $stock->branch_id,
                'stock' => $stock->stock,
                'reserved' => $stock->reserved,
            ];
        });

        return $this->success($inventory, 'Inventory summary generated.');
    }

    public function salesPdf(Request $request)
    {
        $payload = $this->salesReportPayload($request);
        return Pdf::loadHTML($this->reportHtml('Reporte de ventas', $payload))->download('reporte-ventas.pdf');
    }

    public function purchasesPdf(Request $request)
    {
        $payload = $this->purchaseReportPayload($request);
        return Pdf::loadHTML($this->reportHtml('Reporte de compras', $payload))->download('reporte-compras.pdf');
    }

    public function inventoryPdf(Request $request)
    {
        $payload = $this->inventoryReportPayload($request);
        return Pdf::loadHTML($this->reportHtml('Reporte de inventario', $payload))->download('reporte-inventario.pdf');
    }

    public function movementsPdf(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $tenantId = $this->tenantIdFromUser($user);
        $query = \App\Models\StockMovement::query();
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->where('tenant_id', '__no_tenant__');
        }

        $movements = $query->latest()->limit(100)->get()->map(fn ($movement) => [
            'producto' => $movement->product_id,
            'tipo' => $movement->movement_type,
            'cantidad' => $movement->quantity,
            'fecha' => (string) ($movement->created_at ?? now()),
        ])->all();

        return Pdf::loadHTML($this->reportHtml('Reporte de movimientos', $movements))->download('reporte-movimientos.pdf');
    }

    public function logsPdf(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $tenantId = $this->tenantIdFromUser($user);
        $query = LogEntry::query();
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->where('tenant_id', '__no_tenant__');
        }

        $logs = $query->latest()->limit(100)->get()->map(fn ($log) => [
            'fecha' => (string) ($log->created_at ?? now()),
            'accion' => $log->action,
            'modulo' => $log->auditable_type ?? '-',
            'usuario' => $log->user_id ?? '-',
        ])->all();

        return Pdf::loadHTML($this->reportHtml('Bitácora', $logs))->download('bitacora.pdf');
    }

    protected function salesReportPayload(Request $request): array
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return [];
        }

        $tenantId = $this->tenantIdFromUser($user);
        $query = Sale::query();
        $query->where('tenant_id', $tenantId ?: '__no_tenant__');
        $sales = $query->latest()->limit(100)->get();

        return [
            'ventas' => $sales->map(fn ($sale) => [
                'folio' => $sale->reference,
                'cliente' => $sale->client_id,
                'fecha' => (string) ($sale->sold_at ?? $sale->created_at ?? now()),
                'total' => $sale->total,
                'estado' => $sale->status,
            ])->all(),
        ];
    }

    protected function purchaseReportPayload(Request $request): array
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return [];
        }

        $tenantId = $this->tenantIdFromUser($user);
        $query = Purchase::query();
        $query->where('tenant_id', $tenantId ?: '__no_tenant__');
        $purchases = $query->latest()->limit(100)->get();

        return [
            'compras' => $purchases->map(fn ($purchase) => [
                'folio' => $purchase->reference,
                'proveedor' => $purchase->supplier_id,
                'fecha' => (string) ($purchase->purchased_at ?? $purchase->created_at ?? now()),
                'total' => $purchase->total,
                'estado' => $purchase->status,
            ])->all(),
        ];
    }

    protected function inventoryReportPayload(Request $request): array
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return [];
        }

        $tenantId = $this->tenantIdFromUser($user);
        $products = Product::query()->with(['category'])->where('tenant_id', $tenantId ?: '__no_tenant__')->get();

        return [
            'inventario' => $products->map(fn ($product) => [
                'codigo' => $product->sku,
                'nombre' => $product->name,
                'categoria' => $product->category?->name,
                'costo' => $product->cost,
                'precio' => $product->price,
            ])->all(),
        ];
    }

    protected function reportHtml(string $title, array $payload): string
    {
        if (array_is_list($payload) && isset($payload[0]) && is_array($payload[0])) {
            $payload = ['datos' => $payload];
        }

        $rows = '';
        foreach ($payload as $section => $items) {
            if (!is_array($items)) {
                continue;
            }
            $rows .= '<h3>'.htmlspecialchars(ucfirst($section)).'</h3><table width="100%" border="1" cellspacing="0" cellpadding="6">';
            if (count($items) > 0) {
                $headers = array_keys($items[0]);
                $rows .= '<tr>';
                foreach ($headers as $header) {
                    $rows .= '<th>'.htmlspecialchars((string) $header).'</th>';
                }
                $rows .= '</tr>';
                foreach ($items as $item) {
                    $rows .= '<tr>';
                    foreach ($headers as $header) {
                        $rows .= '<td>'.htmlspecialchars((string) ($item[$header] ?? '')).'</td>';
                    }
                    $rows .= '</tr>';
                }
            } else {
                $rows .= '<tr><td>Sin datos</td></tr>';
            }
            $rows .= '</table><br>';
        }

        return '<html><head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif;font-size:12px} h1{color:#0f172a} table{border-collapse:collapse;margin-bottom:16px} th{background:#e2e8f0}</style></head><body><h1>'.htmlspecialchars($title).'</h1>'.$rows.'</body></html>';
    }
}
