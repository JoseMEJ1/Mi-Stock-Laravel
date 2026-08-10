<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class LicenseSubscription extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'period',
        'start_date',
        'end_date',
        'status',
        'payment_method',
        'auto_renew',
        'amount',
        'currency',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'auto_renew' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'cancelled_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(LicenseTenant::class, 'tenant_id');
    }

    public function plan()
    {
        return $this->belongsTo(LicensePlan::class, 'plan_id');
    }

    public function invoices()
    {
        return $this->hasMany(LicenseInvoice::class, 'subscription_id');
    }
}
