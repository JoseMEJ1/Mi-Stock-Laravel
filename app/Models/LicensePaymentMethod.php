<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class LicensePaymentMethod extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'key',
        'methods',
    ];

    protected $casts = [
        'methods' => 'array',
    ];
}
