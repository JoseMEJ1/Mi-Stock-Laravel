<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class ProductBranch extends Eloquent
{
    use HasFactory;

    protected $table = 'product_branch';

    protected $fillable = ['product_id','branch_id','stock','reserved'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
