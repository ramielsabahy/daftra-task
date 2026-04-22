<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\IndexInventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Http\Responses\ApiResponse;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    /**
     * GET /api/v1/inventory
     *
     * Filters: warehouse_id, item_id, sku, low_stock, per_page
     */
    public function index(IndexInventoryRequest $request): JsonResponse
    {
        $paginator = $this->inventoryService->list($request->validated());

        return ApiResponse::success(
            InventoryResource::collection($paginator),
            'Inventory retrieved successfully'
        );
    }

    /**
     * GET /api/v1/inventory/{id}
     */
    public function show(int $id): JsonResponse
    {
        $inventory = $this->inventoryService->find($id);

        return ApiResponse::success(
            new InventoryResource($inventory),
            'Inventory line retrieved successfully'
        );
    }
}
