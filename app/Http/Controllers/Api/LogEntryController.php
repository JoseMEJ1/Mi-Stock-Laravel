<?php

namespace App\Http\Controllers\Api;

use App\Models\LogEntry;

class LogEntryController extends CrudController
{
    protected string $modelClass = LogEntry::class;
    protected bool $tenantScoped = true;
}
