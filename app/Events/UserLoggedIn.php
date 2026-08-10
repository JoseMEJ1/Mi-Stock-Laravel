<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoggedIn
{
    use Dispatchable, SerializesModels;

    public $user;
    public $timestamp;

    public function __construct($user)
    {
        $this->user = $user;
        $this->timestamp = now()->toISOString();
    }
}
