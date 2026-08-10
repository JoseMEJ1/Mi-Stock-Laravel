<?php

namespace App\Http\Controllers\Api\Licenses;

use App\Http\Controllers\Api\ApiController;
use App\Models\LicensePlan;
use App\Models\LicenseSubscription;
use App\Models\LicenseTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LicenseDashboardController extends ApiController
{
    public function dashboard(Request $request)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $now = Carbon::now();
        $activeSubscriptions = LicenseSubscription::where('status', 'active')->count();
        $totalTenants = LicenseTenant::count();
        $monthlyRevenue = LicenseSubscription::where('status', 'active')
            ->where('period', 'monthly')
            ->sum('amount');

        $expiringSoon = LicenseSubscription::where('status', 'active')
            ->whereBetween('end_date', [$now->toDateString(), $now->copy()->addDays(30)->toDateString()])
            ->count();

        $subscriptionsByPlan = LicenseSubscription::where('status', 'active')
            ->get()
            ->groupBy('plan_id')
            ->map(function ($items, $planId) {
                $plan = LicensePlan::find($planId);
                return [
                    'plan' => $plan?->name ?? 'Unknown',
                    'count' => $items->count(),
                ];
            })->values();

        $revenueByMonth = LicenseSubscription::where('status', 'active')
            ->where('start_date', '>=', $now->copy()->subMonths(2)->startOfMonth()->toDateString())
            ->get()
            ->groupBy(function ($subscription) {
                return Carbon::parse($subscription->start_date)->format('Y-m');
            })->map(fn ($items, $month) => [
                'month' => $month,
                'revenue' => $items->sum('amount'),
            ])->values();

        return $this->success([
            'metrics' => [
                'total_tenants' => $totalTenants,
                'active_subscriptions' => $activeSubscriptions,
                'monthly_revenue' => round($monthlyRevenue, 2),
                'monthly_growth' => 0.0,
                'renewal_rate' => 0.0,
                'expiring_soon' => $expiringSoon,
            ],
            'charts' => [
                'subscriptions_by_plan' => $subscriptionsByPlan,
                'revenue_by_month' => $revenueByMonth,
            ],
        ]);
    }
}
