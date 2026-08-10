<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class LicenseTenant extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'name',
        'commercial_name',
        'rfc',
        'email',
        'phone',
        'address',
        'fiscal_address',
        'fiscal_regime',
        'plan_id',
        'period',
        'status',
    ];

    public function subscriptions()
    {
        return $this->hasMany(LicenseSubscription::class, 'tenant_id');
    }
}
