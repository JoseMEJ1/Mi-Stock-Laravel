<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class LicensePlan extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'max_users',
        'max_branches',
        'price_monthly',
        'price_semester',
        'price_annual',
        'features',
        'modules',
        'status',
    ];

    protected $casts = [
        'max_users' => 'integer',
        'max_branches' => 'integer',
        'price_monthly' => 'decimal:2',
        'price_semester' => 'decimal:2',
        'price_annual' => 'decimal:2',
        'features' => 'array',
        'modules' => 'array',
    ];

    public function subscriptions()
    {
        return $this->hasMany(LicenseSubscription::class, 'plan_id');
    }
}
