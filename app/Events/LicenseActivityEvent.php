<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LicenseActivityEvent
{
    use Dispatchable, SerializesModels;

    public string $resource;
    public string $action;
    public ?string $userId;
    public ?string $entityId;
    public array $data;

    public function __construct(string $resource, string $action, ?string $userId = null, ?string $entityId = null, array $data = [])
    {
        $this->resource = $resource;
        $this->action = $action;
        $this->userId = $userId;
        $this->entityId = $entityId;
        $this->data = $data;
    }
}
