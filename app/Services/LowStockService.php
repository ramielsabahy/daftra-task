<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\LowStockEvent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LowStockService
{
    /**
     * Evaluate whether the given inventory line breaches its low stock threshold.
     * Creates a LowStockEvent only if no unresolved event already exists.
     */
    public function evaluate(Inventory $inventory): void
    {
        // Reload fresh data after transfer
        $inventory->refresh()->load('item');

        if (!$this->isThresholdEnabled($inventory)) {
            return;
        }

        if (!$inventory->isLowStock()) {
            // Stock was replenished — resolve any open event
            $this->resolveIfExists($inventory);
            return;
        }

        // Avoid duplicate events for the same inventory line
        $existingOpen = LowStockEvent::where('inventory_id', $inventory->id)
            ->unresolved()
            ->exists();

        if ($existingOpen) {
            return;
        }

        LowStockEvent::create([
            'inventory_id' => $inventory->id,
            'triggered_at' => now(),
            'notified'     => false,
        ]);

        // TODO: Dispatch LowStockNotification (Mail / Slack / webhook)
        // Notification::send(...);
    }

    /**
     * Mark the open low stock event for this inventory as resolved.
     */
    public function resolveIfExists(Inventory $inventory): void
    {
        LowStockEvent::where('inventory_id', $inventory->id)
            ->unresolved()
            ->update(['resolved_at' => now()]);
    }

    /**
     * Return all currently active (unresolved) low stock alerts, paginated.
     */
    public function activeAlerts(int $perPage = 25): LengthAwarePaginator
    {
        return LowStockEvent::with(['inventory.item', 'inventory.warehouse'])
            ->unresolved()
            ->latest('triggered_at')
            ->paginate($perPage);
    }

    /**
     * Low-stock detection is disabled when threshold is 0.
     */
    private function isThresholdEnabled(Inventory $inventory): bool
    {
        return $inventory->item->low_stock_threshold > 0;
    }
}
