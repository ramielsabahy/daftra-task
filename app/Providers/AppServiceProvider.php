<?php

namespace App\Providers;

use App\Events\LowStockDetectedEvent;
use App\Listeners\LowStockListener;
use App\Repositories\InventoryRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository so it can be injected via the container
        $this->app->singleton(InventoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event → queued listener mapping
        Event::listen(LowStockDetectedEvent::class, LowStockListener::class);

        // Ensure JSON resources don't wrap in a redundant "data" key
        // when we manually wrap via ApiResponse
        \Illuminate\Http\Resources\Json\JsonResource::withoutWrapping();
    }
}
