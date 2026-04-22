<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Http\Responses\ApiResponse;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * GET /api/v1/items
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 25), 100);

        $items = Item::query()
            ->when($request->query('sku'), fn($q, $sku) => $q->where('sku', $sku))
            ->when($request->query('name'), fn($q, $name) => $q->where('name', 'like', "%{$name}%"))
            ->latest()
            ->paginate($perPage);

        return ApiResponse::success(
            ItemResource::collection($items),
            'Items retrieved successfully'
        );
    }

    /**
     * POST /api/v1/items
     */
    public function store(StoreItemRequest $request): JsonResponse
    {
        $item = Item::create($request->validated());

        return ApiResponse::created(
            new ItemResource($item),
            'Item created successfully'
        );
    }

    /**
     * GET /api/v1/items/{item}
     */
    public function show(Item $item): JsonResponse
    {
        return ApiResponse::success(
            new ItemResource($item),
            'Item retrieved successfully'
        );
    }

    /**
     * PUT /api/v1/items/{item}
     */
    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        $item->update($request->validated());

        return ApiResponse::success(
            new ItemResource($item->fresh()),
            'Item updated successfully'
        );
    }

    /**
     * DELETE /api/v1/items/{item}
     */
    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return ApiResponse::success(message: 'Item deleted successfully');
    }
}
