<?php

namespace App\Listeners;

use App\Events\LowStockDetectedEvent;
use App\Services\LowStockService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LowStockListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Queued listener — does not block the HTTP response.
     * Retries up to 3 times on failure.
     */
    public int $tries = 3;

    public function __construct(protected LowStockService $lowStockService) {}

    public function handle(LowStockDetectedEvent $event): void
    {
        $this->lowStockService->evaluate($event->inventory);
    }
}
