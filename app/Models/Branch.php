<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Branch extends Eloquent
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','code','address','phone','is_main','tenant_id'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_branch')->withPivot(['stock','reserved'])->withTimestamps();
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
