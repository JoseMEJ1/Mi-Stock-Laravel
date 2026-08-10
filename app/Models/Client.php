<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Client extends Eloquent
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','email','phone','address','tax_id'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
