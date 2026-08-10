<?php

namespace App\Http\Controllers\Api\Licenses;

use App\Http\Controllers\Api\ApiController;
use App\Models\LicensePlan;
use App\Models\LicenseSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LicenseReportController extends ApiController
{
    public function revenue(Request $request)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $period = $request->input('period', 'monthly');
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : Carbon::now()->endOfMonth();

        $query = LicenseSubscription::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->input('plan_id'));
        }

        $subscriptions = $query->whereBetween('start_date', [$from->toDateString(), $to->toDateString()])->get();
        $totalRevenue = $subscriptions->sum('amount');

        $revenueByPlan = $subscriptions->groupBy('plan_id')->map(function ($items, $planId) {
            $plan = LicensePlan::find($planId);
            return [
                'plan_name' => $plan?->name ?? 'Unknown',
                'total' => $items->sum('amount'),
                'count' => $items->count(),
            ];
        })->values();

        $newSubscriptions = $subscriptions->filter(function ($subscription) use ($from, $to) {
            return $subscription->created_at && $subscription->created_at->between($from, $to);
        })->count();

        $renewals = $subscriptions->filter(function ($subscription) use ($from, $to) {
            return $subscription->updated_at && $subscription->created_at && $subscription->updated_at->between($from, $to) && $subscription->updated_at->gt($subscription->created_at);
        })->count();

        $previousPeriod = $this->previousPeriodRange($from, $period);
        $previousRevenue = LicenseSubscription::whereBetween('start_date', [$previousPeriod['from']->toDateString(), $previousPeriod['to']->toDateString()])->sum('amount');
        $growthRate = $previousRevenue > 0 ? round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 2) : 0;

        return $this->success([
            'period' => $period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total_revenue' => round($totalRevenue, 2),
            'revenue_by_plan' => $revenueByPlan,
            'new_subscriptions' => $newSubscriptions,
            'renewals' => $renewals,
            'cancellations' => LicenseSubscription::whereBetween('start_date', [$from->toDateString(), $to->toDateString()])->where('status', 'cancelled')->count(),
            'projected_revenue' => round($totalRevenue * 1.05, 2),
            'growth_rate' => $growthRate,
        ]);
    }

    protected function previousPeriodRange(Carbon $from, string $period): array
    {
        return match ($period) {
            'yearly' => [
                'from' => $from->copy()->subYear()->startOfYear(),
                'to' => $from->copy()->subYear()->endOfYear(),
            ],
            'quarterly' => [
                'from' => $from->copy()->subMonths(3)->startOfMonth(),
                'to' => $from->copy()->subMonths(1)->endOfMonth(),
            ],
            default => [
                'from' => $from->copy()->subMonth()->startOfMonth(),
                'to' => $from->copy()->subMonth()->endOfMonth(),
            ],
        };
    }
}
