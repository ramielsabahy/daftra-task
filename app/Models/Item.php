<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'unit',
        'low_stock_threshold',
    ];

    protected $casts = [
        'low_stock_threshold' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeBySku($query, string $sku)
    {
        return $query->where('sku', $sku);
    }
}
