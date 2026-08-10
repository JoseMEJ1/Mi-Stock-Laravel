<?php

namespace App\Http\Controllers\Api\Licenses;

use App\Events\LicenseActivityEvent;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\StoreLicenseTenantRequest;
use App\Http\Requests\UpdateLicenseTenantRequest;
use App\Models\LicenseInvoice;
use App\Models\LicenseSubscription;
use App\Models\LicenseTenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LicenseTenantController extends ApiController
{
    public function index(Request $request)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $total = LicenseTenant::count();
        $items = LicenseTenant::skip(($page - 1) * $limit)->take($limit)->get();

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

        $tenant = LicenseTenant::find($id);
        if (!$tenant) {
            return $this->error('Tenant not found.', 404);
        }

        return $this->success($tenant);
    }

    public function store(StoreLicenseTenantRequest $request)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $tenant = LicenseTenant::create(array_merge($request->validated(), [
            'status' => $request->input('status', 'active'),
        ]));

        $this->logAction($user, 'created license tenant', $tenant, $tenant->toArray());
        event(new LicenseActivityEvent('license_tenant', 'created', $user->getKey(), (string) $tenant->getKey(), $tenant->toArray()));
        $this->broadcastModelChange($tenant, 'created');

        return $this->success($tenant, 'Tenant created.', 201);
    }

    public function update(UpdateLicenseTenantRequest $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $tenant = LicenseTenant::find($id);
        if (!$tenant) {
            return $this->error('Tenant not found.', 404);
        }

        $tenant->fill($request->validated());
        $tenant->save();

        $this->logAction($user, 'updated license tenant', $tenant, $tenant->toArray());
        event(new LicenseActivityEvent('license_tenant', 'updated', $user->getKey(), (string) $tenant->getKey(), $tenant->toArray()));
        $this->broadcastModelChange($tenant, 'updated');

        return $this->success($tenant, 'Tenant updated.');
    }

    public function status(Request $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $tenant = LicenseTenant::find($id);
        if (!$tenant) {
            return $this->error('Tenant not found.', 404);
        }

        $status = $request->input('status');
        if (!in_array($status, ['active', 'inactive'], true)) {
            return $this->error('Invalid tenant status.', 422);
        }

        $tenant->status = $status;
        $tenant->save();

        $this->logAction($user, 'changed tenant status', $tenant, ['status' => $status]);
        event(new LicenseActivityEvent('license_tenant', 'status_changed', $user->getKey(), (string) $tenant->getKey(), ['status' => $status]));
        $this->broadcastModelChange($tenant, 'updated');

        return $this->success($tenant, 'Tenant status updated.');
    }

    public function paymentHistory(Request $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $tenant = LicenseTenant::find($id);
        if (!$tenant) {
            return $this->error('Tenant not found.', 404);
        }

        $history = LicenseSubscription::where('tenant_id', (string) $tenant->getKey())
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(['items' => $history]);
    }

    public function invoices(Request $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $tenant = LicenseTenant::find($id);
        if (!$tenant) {
            return $this->error('Tenant not found.', 404);
        }

        $subscriptionIds = LicenseSubscription::where('tenant_id', (string) $tenant->getKey())
            ->pluck('_id')
            ->toArray();

        $invoices = LicenseInvoice::whereIn('subscription_id', $subscriptionIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(['items' => $invoices]);
    }
}
