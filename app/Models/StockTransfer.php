<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StockTransfer extends Model
{
    use HasFactory;

    const STATUS_PENDING   = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED    = 'failed';

    protected $fillable = [
        'from_warehouse_id',
        'to_warehouse_id',
        'item_id',
        'transferred_by',
        'quantity',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function log(): HasOne
    {
        return $this->hasOne(StockTransferLog::class, 'transfer_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('from_warehouse_id', $warehouseId)
                     ->orWhere('to_warehouse_id', $warehouseId);
    }
}
