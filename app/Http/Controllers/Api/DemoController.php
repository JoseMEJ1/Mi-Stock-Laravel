<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;

class DemoController extends Controller
{
    /**
     * Return a lightweight summary useful for demos.
     * Public (no auth) so the frontend demo can fetch quick counts.
     */
    public function summary(Request $request)
    {
        $since = Carbon::now()->subDays(7);

        $counts = [
            'users' => null,
            'branches' => null,
            'products' => null,
            'purchases_last_7_days' => null,
            'sales_last_7_days' => null,
            'movements_last_7_days' => null,
        ];

        $recentMovements = [];
        $sampleUsers = [];
        $errors = [];

        try {
            $counts['users'] = User::count();
            $counts['branches'] = Branch::count();
            $counts['products'] = Product::count();
            $counts['purchases_last_7_days'] = Purchase::where('purchased_at', '>=', $since)->count();
            $counts['sales_last_7_days'] = Sale::where('sold_at', '>=', $since)->count();
            $counts['movements_last_7_days'] = StockMovement::where('created_at', '>=', $since)->count();

            $recentMovements = StockMovement::orderBy('created_at', 'desc')->limit(10)->get()->map(function($m){
                return [
                    'id' => (string) ($m->_id ?? $m->id ?? null),
                    'product_id' => $m->product_id ?? null,
                    'branch_id' => $m->branch_id ?? null,
                    'user_id' => $m->user_id ?? null,
                    'type' => $m->movement_type ?? $m->type ?? null,
                    'quantity' => $m->quantity ?? null,
                    'created_at' => isset($m->created_at) ? (string)$m->created_at : null,
                ];
            })->toArray();

            $sampleUsers = User::orderBy('created_at', 'desc')->limit(5)->get(['name','email'])->toArray();
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }

        return response()->json([
            'counts' => $counts,
            'recent_movements' => $recentMovements,
            'sample_users' => $sampleUsers,
            'errors' => $errors,
            'generated_at' => (string) Carbon::now(),
        ], 200);
    }
}
