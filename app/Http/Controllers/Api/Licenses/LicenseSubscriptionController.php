<?php

namespace App\Http\Controllers\Api\Licenses;

use App\Events\LicenseActivityEvent;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\CancelLicenseSubscriptionRequest;
use App\Http\Requests\RenewLicenseSubscriptionRequest;
use App\Http\Requests\StoreLicenseSubscriptionRequest;
use App\Models\LicenseInvoice;
use App\Models\LicensePlan;
use App\Models\LicenseSubscription;
use App\Models\LicenseTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LicenseSubscriptionController extends ApiController
{
    public function index(Request $request)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $query = LicenseSubscription::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->input('plan_id'));
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->input('tenant_id'));
        }

        if ($request->filled('from')) {
            $query->where('start_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('end_date', '<=', $request->input('to'));
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $total = $query->count();
        $items = $query->skip(($page - 1) * $limit)->take($limit)->get();

        return $this->success([
            'items' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => (int) ceil($total / $limit),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $subscription = LicenseSubscription::find($id);
        if (!$subscription) {
            return $this->error('Subscription not found.', 404);
        }

        return $this->success($subscription);
    }

    public function store(StoreLicenseSubscriptionRequest $request)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $data = $request->validated();
        $tenant = LicenseTenant::find($data['tenant_id']);
        if (!$tenant) {
            return $this->error('Tenant not found.', 404);
        }

        $plan = LicensePlan::find($data['plan_id']);
        if (!$plan) {
            return $this->error('Plan not found.', 404);
        }

        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();
        $period = $data['period'];
        $endDate = $this->calculateEndDate($startDate, $period);
        $amount = $this->getAmountForPeriod($plan, $period);

        $subscription = LicenseSubscription::create([
            'tenant_id' => (string) $tenant->getKey(),
            'plan_id' => (string) $plan->getKey(),
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => 'active',
            'payment_method' => $data['payment_method'],
            'auto_renew' => $data['auto_renew'] ?? false,
            'amount' => $amount,
            'currency' => 'MXN',
        ]);

        $this->logAction($user, 'created subscription', $subscription, $subscription->toArray());
        event(new LicenseActivityEvent('license_subscription', 'created', $user->getKey(), (string) $subscription->getKey(), $subscription->toArray()));
        $this->broadcastModelChange($subscription, 'created');

        return $this->success($subscription, 'Subscription created.', 201);
    }

    public function renew(RenewLicenseSubscriptionRequest $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $subscription = LicenseSubscription::find($id);
        if (!$subscription) {
            return $this->error('Subscription not found.', 404);
        }

        $plan = LicensePlan::find($subscription->plan_id);
        if (!$plan) {
            return $this->error('Subscription plan not found.', 404);
        }

        $period = $request->input('period', $subscription->period);
        $paymentMethod = $request->input('payment_method');
        $startDate = Carbon::now();
        if ($subscription->end_date && Carbon::parse($subscription->end_date)->isFuture()) {
            $startDate = Carbon::parse($subscription->end_date);
        }

        $endDate = $this->calculateEndDate($startDate, $period);
        $amount = $this->getAmountForPeriod($plan, $period);

        $subscription->period = $period;
        $subscription->payment_method = $paymentMethod;
        $subscription->auto_renew = $subscription->auto_renew;
        $subscription->status = 'active';
        $subscription->start_date = $startDate->toDateString();
        $subscription->end_date = $endDate->toDateString();
        $subscription->amount = $amount;
        $subscription->currency = 'MXN';
        $subscription->save();

        $this->logAction($user, 'renewed subscription', $subscription, $subscription->toArray());
        event(new LicenseActivityEvent('license_subscription', 'renewed', $user->getKey(), (string) $subscription->getKey(), $subscription->toArray()));
        $this->broadcastModelChange($subscription, 'updated');

        return $this->success([
            'id' => (string) $subscription->getKey(),
            'new_end_date' => $subscription->end_date,
            'renewal_date' => $subscription->start_date,
            'amount' => $subscription->amount,
            'invoice_id' => null,
        ], 'Subscription renewed successfully.');
    }

    public function destroy(CancelLicenseSubscriptionRequest $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $subscription = LicenseSubscription::find($id);
        if (!$subscription) {
            return $this->error('Subscription not found.', 404);
        }

        $subscription->status = 'cancelled';
        $subscription->cancellation_reason = $request->input('cancellation_reason');
        $subscription->cancelled_at = Carbon::now();
        $subscription->save();

        $this->logAction($user, 'cancelled subscription', $subscription, $subscription->toArray());
        event(new LicenseActivityEvent('license_subscription', 'cancelled', $user->getKey(), (string) $subscription->getKey(), $subscription->toArray()));
        $this->broadcastModelChange($subscription, 'updated');

        return $this->success([
            'id' => (string) $subscription->getKey(),
            'status' => 'cancelled',
            'access_until' => $subscription->end_date,
            'cancellation_reason' => $subscription->cancellation_reason,
        ], 'Subscription cancelled successfully.');
    }

    public function invoice(Request $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $subscription = LicenseSubscription::find($id);
        if (!$subscription) {
            return $this->error('Subscription not found.', 404);
        }

        $amount = $subscription->amount;
        $taxRate = 16;
        $subtotal = round($amount / (1 + $taxRate / 100), 2);
        $taxAmount = round($amount - $subtotal, 2);

        $invoice = LicenseInvoice::create([
            'subscription_id' => (string) $subscription->getKey(),
            'folio' => strtoupper('INV-' . Str::random(6)),
            'series' => 'A',
            'rfc' => 'RFC000000000',
            'business_name' => 'Mi-Stock Tenant',
            'concept' => 'Subscription invoice',
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $amount,
            'currency' => $subscription->currency,
            'cfdi_pdf_url' => url('/invoices/' . ($subscription->getKey()) . '.pdf'),
            'cfdi_xml_url' => url('/invoices/' . ($subscription->getKey()) . '.xml'),
            'status' => 'issued',
            'issued_at' => Carbon::now(),
        ]);

        $this->logAction($user, 'generated subscription invoice', $invoice, $invoice->toArray());
        event(new LicenseActivityEvent('license_invoice', 'generated', $user->getKey(), (string) $invoice->getKey(), $invoice->toArray()));
        $this->broadcastModelChange($invoice, 'created');

        return $this->success($invoice, 'Invoice generated successfully.');
    }

    public function remind(Request $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $subscription = LicenseSubscription::find($id);
        if (!$subscription) {
            return $this->error('Subscription not found.', 404);
        }

        $daysBefore = (int) $request->input('days_before', 15);
        event(new LicenseActivityEvent('license_subscription', 'remind', $user->getKey(), (string) $subscription->getKey(), ['days_before' => $daysBefore]));

        return $this->success(['days_before' => $daysBefore], 'Subscription reminder sent.');
    }

    protected function calculateEndDate(Carbon $startDate, string $period): Carbon
    {
        return match ($period) {
            'annual' => $startDate->copy()->addYear(),
            'semester' => $startDate->copy()->addMonths(6),
            default => $startDate->copy()->addMonth(),
        };
    }

    protected function getAmountForPeriod(LicensePlan $plan, string $period): float
    {
        return match ($period) {
            'annual' => (float) $plan->price_annual,
            'semester' => (float) $plan->price_semester,
            default => (float) $plan->price_monthly,
        };
    }
}
