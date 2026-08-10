<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleCreated
{
    use Dispatchable, SerializesModels;

    public $sale;
    public $timestamp;

    public function __construct(array $sale)
    {
        $this->sale = $sale;
        $this->timestamp = now()->toISOString();
    }
}
