<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'quantity'       => $this->quantity,
            'status'         => $this->status,
            'notes'          => $this->notes,
            'from_warehouse' => new WarehouseResource($this->whenLoaded('fromWarehouse')),
            'to_warehouse'   => new WarehouseResource($this->whenLoaded('toWarehouse')),
            'item'           => new ItemResource($this->whenLoaded('item')),
            'transferred_by' => $this->whenLoaded('transferredBy', fn() => [
                'id'    => $this->transferredBy->id,
                'name'  => $this->transferredBy->name,
                'email' => $this->transferredBy->email,
            ]),
            'audit_log' => $this->whenLoaded('log', fn() => [
                'old_qty_from' => $this->log->old_qty_from,
                'old_qty_to'   => $this->log->old_qty_to,
                'logged_at'    => $this->log->created_at?->toIso8601String(),
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
