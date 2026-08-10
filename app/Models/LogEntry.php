<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class LogEntry extends Eloquent
{
    use HasFactory;

    protected $table = 'logs';

    protected $fillable = ['user_id','action','auditable_type','auditable_id','data'];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
