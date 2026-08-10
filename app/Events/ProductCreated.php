<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductCreated
{
    use Dispatchable, SerializesModels;

    public $product;
    public $timestamp;

    public function __construct(array $product)
    {
        $this->product = $product;
        $this->timestamp = now()->toISOString();
    }
}
