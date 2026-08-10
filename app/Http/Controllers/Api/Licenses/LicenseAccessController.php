<?php

namespace App\Http\Controllers\Api\Licenses;

use App\Http\Controllers\Api\ApiController;
use App\Models\LicenseSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LicenseAccessController extends ApiController
{
    public function checkAccess(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $tenantId = $user->tenant_id ?? $request->query('tenant_id');
        if (!$tenantId) {
            return $this->error('No tenant assigned for user.', 400);
        }

        $subscription = LicenseSubscription::where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->first();

        if (!$subscription) {
            return $this->error('No active subscription found.', 403, ['has_access' => false]);
        }

        $today = Carbon::now();
        $endDate = Carbon::parse($subscription->end_date);
        $hasAccess = $subscription->status === 'active' && $endDate->isFuture();

        if ($hasAccess) {
            return $this->success([
                'has_access' => true,
                'subscription' => [
                    'plan' => $subscription->plan?->name ?? 'Unknown',
                    'expires_at' => $endDate->toDateString(),
                    'days_remaining' => $today->diffInDays($endDate, false),
                ],
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Su suscripción ha vencido. Contacte a su administrador para renovar.',
            'data' => [
                'has_access' => false,
                'subscription' => [
                    'plan' => $subscription->plan?->name ?? 'Unknown',
                    'expired_at' => $endDate->toDateString(),
                    'days_overdue' => $today->diffInDays($endDate),
                ],
                'renewal_url' => '/renovar-suscripcion',
            ],
        ], 403);
    }
}
