<?php

namespace App\Repositories;

use App\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InventoryRepository
{
    /**
     * Build a filtered, paginated inventory query.
     *
     * Supported filters:
     *  - warehouse_id  : int
     *  - item_id       : int
     *  - sku           : string (searches via item relationship)
     *  - low_stock     : bool   (items at or below threshold)
     *  - per_page      : int    (default 25, max 100)
     */
    public function filter(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = Inventory::with(['item', 'warehouse']);

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['sku'])) {
            $query->whereHas('item', fn($q) => $q->where('sku', $filters['sku']));
        }

        if (!empty($filters['low_stock'])) {
            $query->lowStock();
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find an inventory line by primary key or throw a 404.
     */
    public function findOrFail(int $id): Inventory
    {
        return Inventory::with(['item', 'warehouse'])->findOrFail($id);
    }
}
