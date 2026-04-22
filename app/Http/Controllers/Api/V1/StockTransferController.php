<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\StoreTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Http\Responses\ApiResponse;
use App\Models\StockTransfer;
use App\Services\StockTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    public function __construct(protected StockTransferService $transferService) {}

    /**
     * GET /api/v1/transfers
     *
     * Uses cursor pagination to avoid COUNT(*) overhead on large tables.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 25), 100);

        $transfers = StockTransfer::with([
                'fromWarehouse', 'toWarehouse', 'item', 'transferredBy', 'log',
            ])
            ->when($request->query('warehouse_id'), fn($q, $id) =>
                $q->where('from_warehouse_id', $id)->orWhere('to_warehouse_id', $id)
            )
            ->when($request->query('item_id'), fn($q, $id) => $q->where('item_id', $id))
            ->when($request->query('status'), fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->cursorPaginate($perPage);

        return ApiResponse::success(
            StockTransferResource::collection($transfers),
            'Transfers retrieved successfully'
        );
    }

    /**
     * POST /api/v1/transfers
     */
    public function store(StoreTransferRequest $request): JsonResponse
    {
        $transfer = $this->transferService->transfer(
            $request->validated(),
            $request->user()
        );

        return ApiResponse::created(
            new StockTransferResource($transfer),
            'Stock transfer completed successfully'
        );
    }

    /**
     * GET /api/v1/transfers/{transfer}
     */
    public function show(StockTransfer $transfer): JsonResponse
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'item', 'transferredBy', 'log']);

        return ApiResponse::success(
            new StockTransferResource($transfer),
            'Transfer retrieved successfully'
        );
    }
}
