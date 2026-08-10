<?php

namespace App\Http\Controllers\Api;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\ProductBranch;
use Illuminate\Http\Request;

class ReportController extends ApiController
{
    public function salesSummary(Request $request)
    {
        $this->authorize($request);

        $query = Sale::query();
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
        $this->authorize($request);

        $query = Purchase::query();
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
        $this->authorize($request);

        $query = ProductBranch::query();
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
}
