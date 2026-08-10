<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class LicenseInvoice extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'folio',
        'series',
        'rfc',
        'business_name',
        'concept',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'currency',
        'cfdi_pdf_url',
        'cfdi_xml_url',
        'status',
        'issued_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(LicenseSubscription::class, 'subscription_id');
    }
}
