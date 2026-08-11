<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class InventorySnapshot extends Eloquent
{
    use HasFactory;

    protected $fillable = ['branch_id','taken_by','tenant_id','snapshot_at','data'];

    protected $casts = [
        'data' => 'array',
        'snapshot_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function taker()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }
}
