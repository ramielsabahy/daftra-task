<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Http\Responses\ApiResponse;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * GET /api/v1/warehouses
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 25), 100);

        $warehouses = Warehouse::query()
            ->when($request->query('is_active') !== null, fn($q) =>
                $q->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN))
            )
            ->latest()
            ->paginate($perPage);

        return ApiResponse::success(
            WarehouseResource::collection($warehouses),
            'Warehouses retrieved successfully'
        );
    }

    /**
     * POST /api/v1/warehouses
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $warehouse = Warehouse::create($request->validated());

        return ApiResponse::created(
            new WarehouseResource($warehouse),
            'Warehouse created successfully'
        );
    }

    /**
     * GET /api/v1/warehouses/{warehouse}
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        return ApiResponse::success(
            new WarehouseResource($warehouse),
            'Warehouse retrieved successfully'
        );
    }

    /**
     * PUT /api/v1/warehouses/{warehouse}
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        $warehouse->update($request->validated());

        return ApiResponse::success(
            new WarehouseResource($warehouse->fresh()),
            'Warehouse updated successfully'
        );
    }

    /**
     * DELETE /api/v1/warehouses/{warehouse}
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $warehouse->delete();

        return ApiResponse::success(message: 'Warehouse deleted successfully');
    }
}
