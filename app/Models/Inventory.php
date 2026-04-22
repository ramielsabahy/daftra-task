<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lowStockEvents(): HasMany
    {
        return $this->hasMany(LowStockEvent::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Whether this inventory line is at or below low stock threshold.
     */
    public function isLowStock(): bool
    {
        if ($this->item->low_stock_threshold <= 0) {
            return false;
        }

        return $this->quantity <= $this->item->low_stock_threshold;
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeLowStock($query)
    {
        return $query->whereHas('item', function ($q) {
            $q->whereColumn('inventories.quantity', '<=', 'items.low_stock_threshold')
              ->where('items.low_stock_threshold', '>', 0);
        });
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }
}
