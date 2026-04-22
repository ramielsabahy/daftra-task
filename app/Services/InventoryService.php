<?php

namespace App\Services;

use App\Models\Inventory;
use App\Repositories\InventoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class InventoryService
{
    public function __construct(protected InventoryRepository $inventoryRepository)
    {
    }

    /**
     * Return a paginated, filtered inventory list — cache-backed.
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $cacheKey = 'inventory:list:' . sha1(json_encode(collect($filters)->sortKeys()->all()));
        $perPage = min((int) ($filters['per_page'] ?? 25), 100);

        return Cache::remember($cacheKey, 60, function () use ($filters, $perPage) {
            return $this->inventoryRepository->filter($filters, $perPage);
        });
    }

    /**
     * Find a single inventory line by ID.
     */
    public function find(int $id): Inventory
    {
        return $this->inventoryRepository->findOrFail($id);
    }

    /**
     * Flush the inventory cache tag.
     * Called after any stock mutation.
     */
    public function flushCache(): void
    {
        Cache::flush();
    }
}
