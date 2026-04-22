<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LowStockEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'triggered_at' => $this->triggered_at?->toIso8601String(),
            'resolved_at'  => $this->resolved_at?->toIso8601String(),
            'is_resolved'  => $this->isResolved(),
            'notified'     => $this->notified,
            'inventory'    => $this->whenLoaded('inventory', fn() => [
                'id'       => $this->inventory->id,
                'quantity' => $this->inventory->quantity,
                'item'     => new ItemResource($this->inventory->item),
                'warehouse' => new WarehouseResource($this->inventory->warehouse),
            ]),
        ];
    }
}
