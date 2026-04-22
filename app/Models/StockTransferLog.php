<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'transfer_id',
        'old_qty_from',
        'old_qty_to',
        'created_at',
    ];

    protected $casts = [
        'old_qty_from' => 'integer',
        'old_qty_to'   => 'integer',
        'created_at'   => 'datetime',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'transfer_id');
    }
}
