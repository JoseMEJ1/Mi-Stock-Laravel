<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Product extends Eloquent
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['sku','name','description','category_id','supplier_id','cost','price','unit','barcode'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'product_branch')->withPivot(['stock','reserved'])->withTimestamps();
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
