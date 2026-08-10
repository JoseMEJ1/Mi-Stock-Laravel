<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\LicenseActivityEvent;
use App\Events\UserLoggedIn;
use App\Events\ProductCreated;
use App\Events\ProductStockUpdated;
use App\Events\SaleCreated;
use App\Listeners\LogLicenseActivity;
use App\Listeners\LogUserLogin;
use App\Listeners\SendLowStockNotification;
use App\Listeners\UpdateProductStock;
use App\Listeners\GenerateSaleInvoice;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserLoggedIn::class => [
            LogUserLogin::class,
        ],

        ProductCreated::class => [
            UpdateProductStock::class,
        ],

        ProductStockUpdated::class => [
            SendLowStockNotification::class,
            UpdateProductStock::class,
        ],

        SaleCreated::class => [
            GenerateSaleInvoice::class,
        ],

        LicenseActivityEvent::class => [
            LogLicenseActivity::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
