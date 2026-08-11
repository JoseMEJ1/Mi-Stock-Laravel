<?php

namespace App\Http\Controllers\Api\Licenses;

use App\Events\LicenseActivityEvent;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ChangeLicensePlanStatusRequest;
use App\Http\Requests\StoreLicensePlanRequest;
use App\Http\Requests\UpdateLicensePlanRequest;
use App\Models\LicensePlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LicensePlanController extends ApiController
{
    protected string $modelClass = LicensePlan::class;

    public function publicIndex(Request $request)
    {
        $query = LicensePlan::query()->where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $this->success([
            'items' => $query->orderBy('name')->get(),
        ]);
    }

    public function index(Request $request)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $query = LicensePlan::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
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

    public function store(StoreLicensePlanRequest $request)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $plan = LicensePlan::create(array_merge($request->validated(), [
            'status' => $request->input('status', 'active'),
        ]));

        $this->logAction($user, 'created license plan', $plan, $plan->toArray());
        event(new LicenseActivityEvent('license_plan', 'created', $user->getKey(), (string) $plan->getKey(), $plan->toArray()));
        $this->broadcastModelChange($plan, 'created');

        return $this->success($plan, 'License plan created.', 201);
    }

    public function update(UpdateLicensePlanRequest $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $plan = LicensePlan::find($id);
        if (!$plan) {
            return $this->error('License plan not found.', 404);
        }

        $plan->fill($request->validated());
        $plan->save();

        $this->logAction($user, 'updated license plan', $plan, $plan->toArray());
        event(new LicenseActivityEvent('license_plan', 'updated', $user->getKey(), (string) $plan->getKey(), $plan->toArray()));
        $this->broadcastModelChange($plan, 'updated');

        return $this->success($plan, 'License plan updated.');
    }

    public function show(Request $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $plan = LicensePlan::find($id);
        if (!$plan) {
            return $this->error('License plan not found.', 404);
        }

        return $this->success($plan);
    }

    public function status(ChangeLicensePlanStatusRequest $request, $id)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $plan = LicensePlan::find($id);
        if (!$plan) {
            return $this->error('License plan not found.', 404);
        }

        $plan->status = $request->input('status');
        $plan->save();

        $this->logAction($user, 'changed license plan status', $plan, $plan->toArray());
        event(new LicenseActivityEvent('license_plan', 'status_changed', $user->getKey(), (string) $plan->getKey(), ['status' => $plan->status]));
        $this->broadcastModelChange($plan, 'updated');

        return $this->success($plan, 'License plan status updated.');
    }
}
