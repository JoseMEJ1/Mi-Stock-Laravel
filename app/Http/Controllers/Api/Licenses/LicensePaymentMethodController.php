<?php

namespace App\Http\Controllers\Api\Licenses;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\UpdateLicensePaymentMethodsRequest;
use App\Models\LicensePaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicensePaymentMethodController extends ApiController
{
    public function publicIndex()
    {
        $methods = LicensePaymentMethod::firstOrCreate([
            'key' => 'default',
        ], [
            'methods' => [
                'card' => ['enabled' => true, 'commission' => 2.5],
                'bank_transfer' => ['enabled' => true, 'commission' => 0],
                'paypal' => ['enabled' => true, 'commission' => 3.5],
                'oxxo' => ['enabled' => true, 'commission' => 5.0],
                'invoice' => ['enabled' => true, 'commission' => 0],
            ],
        ]);

        return $this->success(['methods' => $methods->methods]);
    }

    public function index(Request $request)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $methods = LicensePaymentMethod::firstOrCreate([
            'key' => 'default',
        ], [
            'methods' => [
                'card' => ['enabled' => true, 'commission' => 2.5],
                'bank_transfer' => ['enabled' => true, 'commission' => 0],
                'paypal' => ['enabled' => true, 'commission' => 3.5],
                'oxxo' => ['enabled' => true, 'commission' => 5.0],
                'invoice' => ['enabled' => true, 'commission' => 0],
            ],
        ]);

        return $this->success(['methods' => $methods->methods]);
    }

    public function update(UpdateLicensePaymentMethodsRequest $request)
    {
        $user = $this->authorizeAdmin($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $methods = $request->validated()['methods'];
        $settings = LicensePaymentMethod::firstOrCreate(['key' => 'default']);
        $settings->methods = $methods;
        $settings->save();

        $this->logAction($user, 'updated payment methods', $settings, $settings->toArray());
        $this->broadcastModelChange($settings, 'updated');

        return $this->success(['methods' => $settings->methods], 'Payment methods updated.');
    }
}
