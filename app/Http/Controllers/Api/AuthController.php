<?php

namespace App\Http\Controllers\Api;

use App\Models\LicenseInvoice;
use App\Models\LicensePlan;
use App\Models\LicenseSubscription;
use App\Models\LicenseTenant;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends ApiController
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $license = null;
        $user = null;

        try {
            DB::transaction(function () use (&$user, &$license, $data) {
                $tenant = null;

                if (!empty($data['plan_id']) && !empty($data['period']) && !empty($data['payment_method'])) {
                    $plan = LicensePlan::find($data['plan_id']);
                    if (!$plan) {
                        throw new \RuntimeException('License plan not found.');
                    }

                    $tenantName = $data['business_name'] ?? $data['name'];
                    $tenantRfc = $data['rfc'] ?? $this->generateDefaultRfc($data['email']);

                    $tenant = LicenseTenant::create([
                        'name' => $tenantName,
                        'commercial_name' => $data['business_name'] ?? $tenantName,
                        'rfc' => $tenantRfc,
                        'email' => $data['email'],
                        'status' => 'active',
                        'plan_id' => (string) $plan->getKey(),
                        'period' => $data['period'],
                    ]);

                    $startDate = Carbon::now();
                    $endDate = $this->calculateEndDate($startDate, $data['period']);
                    $amount = $this->getAmountForPeriod($plan, $data['period']);

                    $license = [
                        'tenant' => $tenant,
                        'plan' => $plan,
                        'subscription' => LicenseSubscription::create([
                            'tenant_id' => (string) $tenant->getKey(),
                            'plan_id' => (string) $plan->getKey(),
                            'period' => $data['period'],
                            'start_date' => $startDate->toDateString(),
                            'end_date' => $endDate->toDateString(),
                            'status' => 'active',
                            'payment_method' => $data['payment_method'],
                            'auto_renew' => (bool) ($data['auto_renew'] ?? false),
                            'amount' => $amount,
                            'currency' => 'MXN',
                        ]),
                    ];

                    $license['invoice'] = LicenseInvoice::create([
                        'subscription_id' => (string) $license['subscription']->getKey(),
                        'folio' => strtoupper('INV-' . Str::random(6)),
                        'series' => 'A',
                        'rfc' => $tenantRfc,
                        'business_name' => $tenantName,
                        'concept' => 'License subscription invoice',
                        'subtotal' => round($amount / 1.16, 2),
                        'tax_rate' => 16,
                        'tax_amount' => round($amount - round($amount / 1.16, 2), 2),
                        'total' => $amount,
                        'currency' => 'MXN',
                        'status' => 'issued',
                        'issued_at' => Carbon::now(),
                    ]);
                }

                $userData = $data;
                $userData['password'] = Hash::make($userData['password']);
                $userData['api_token'] = $this->generateToken();
                $userData['tenant_id'] = $tenant ? (string) $tenant->getKey() : null;
                $user = User::create($userData);
            });
        } catch (\Throwable $e) {
            return $this->error($e->getMessage() === 'License plan not found.' ? 'License plan not found.' : 'No fue posible registrar la cuenta.', $e->getMessage() === 'License plan not found.' ? 404 : 500);
        }

        return $this->success([
            'user' => $user,
            'token' => $user->api_token,
            'license' => $license,
        ], 'User registered.', 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return $this->error('Invalid credentials.', 401);
        }

        $user->api_token = $this->generateToken();
        $user->save();

        return $this->success([
            'user' => $user,
            'token' => $user->api_token,
        ], 'Login successful.');
    }

    public function logout(Request $request)
    {
        $user = $this->authorize($request);

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $user->api_token = null;
        $user->save();

        return $this->success(null, 'Logout successful.');
    }

    public function me(Request $request)
    {
        $user = $this->authorize($request);

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        return $this->success($user);
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

    protected function generateDefaultRfc(string $email): string
    {
        return strtoupper('LIC' . substr(hash('sha1', $email), 0, 13));
    }
}
