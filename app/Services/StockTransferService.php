<?php

namespace App\Services;

use App\Events\LowStockDetectedEvent;
use App\Exceptions\InactiveWarehouseException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\SameWarehouseTransferException;
use App\Models\Inventory;
use App\Models\StockTransfer;
use App\Models\StockTransferLog;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    public function __construct(protected InventoryService $inventoryService)
    {
    }

    /**
     * Execute a complete stock transfer with locking, audit log, and post-commit events.
     *
     * @throws SameWarehouseTransferException
     * @throws InactiveWarehouseException
     * @throws InsufficientStockException
     */
    public function transfer(array $data, User $actor): StockTransfer
    {
        $this->guard($data);

        $transfer = DB::transaction(function () use ($data, $actor) {

            // ── 1. Lock source inventory row ──────────────────────────────
            /** @var Inventory $source */
            $source = Inventory::where('item_id', $data['item_id'])
                ->where('warehouse_id', $data['from_warehouse_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // ── 2. Check sufficient stock ─────────────────────────────────
            if ($source->quantity < $data['quantity']) {
                throw new InsufficientStockException($source->quantity, $data['quantity']);
            }

            // ── 3. Lock destination inventory row (create if missing) ─────
            $dest = Inventory::where('item_id', $data['item_id'])
                ->where('warehouse_id', $data['to_warehouse_id'])
                ->lockForUpdate()
                ->first();

            if (!$dest) {
                $dest = Inventory::create([
                    'item_id' => $data['item_id'],
                    'warehouse_id' => $data['to_warehouse_id'],
                    'quantity' => 0,
                ]);
            }

            // Snapshot quantities before mutation for audit log
            $oldQtyFrom = $source->quantity;
            $oldQtyTo = $dest->quantity;

            // ── 4. Debit source / Credit destination ──────────────────────
            $source->decrement('quantity', $data['quantity']);
            $dest->increment('quantity', $data['quantity']);

            // ── 5. Persist transfer record ────────────────────────────────
            $transfer = StockTransfer::create([
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'item_id' => $data['item_id'],
                'transferred_by' => $actor->id,
                'quantity' => $data['quantity'],
                'status' => StockTransfer::STATUS_COMPLETED,
                'notes' => $data['notes'] ?? null,
            ]);

            // ── 6. Immutable audit snapshot ───────────────────────────────
            StockTransferLog::create([
                'transfer_id' => $transfer->id,
                'old_qty_from' => $oldQtyFrom,
                'old_qty_to' => $oldQtyTo,
                'created_at' => now(),
            ]);

            return $transfer;
        });

        // ── Post-commit (outside transaction) ─────────────────────────────
        $source = Inventory::with('item')
            ->where('item_id', $data['item_id'])
            ->where('warehouse_id', $data['from_warehouse_id'])
            ->first();

        if ($source) {
            event(new LowStockDetectedEvent($source));
        }

        $this->inventoryService->flushCache();

        return $transfer->load(['fromWarehouse', 'toWarehouse', 'item', 'transferredBy', 'log']);
    }

    /**
     * Run all domain-level guards before the DB transaction.
     *
     * @throws SameWarehouseTransferException
     * @throws InactiveWarehouseException
     */
    private function guard(array $data): void
    {
        if ($data['from_warehouse_id'] === $data['to_warehouse_id']) {
            throw new SameWarehouseTransferException();
        }

        $warehouses = Warehouse::whereIn('id', [
            $data['from_warehouse_id'],
            $data['to_warehouse_id'],
        ])->get()->keyBy('id');

        /** @var Warehouse|null $from */
        $from = $warehouses->get($data['from_warehouse_id']);
        /** @var Warehouse|null $to */
        $to = $warehouses->get($data['to_warehouse_id']);

        if (!$from) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())
                ->setModel(Warehouse::class, [$data['from_warehouse_id']]);
        }

        if (!$to) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())
                ->setModel(Warehouse::class, [$data['to_warehouse_id']]);
        }

        if (!$from->is_active) {
            throw new InactiveWarehouseException($from->name);
        }

        if (!$to->is_active) {
            throw new InactiveWarehouseException($to->name);
        }
    }
}
