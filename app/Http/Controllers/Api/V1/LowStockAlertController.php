<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LowStockEventResource;
use App\Http\Responses\ApiResponse;
use App\Services\LowStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LowStockAlertController extends Controller
{
    public function __construct(protected LowStockService $lowStockService) {}

    /**
     * GET /api/v1/alerts/low-stock
     *
     * Returns all currently active (unresolved) low stock events, paginated.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 25), 100);

        $alerts = $this->lowStockService->activeAlerts($perPage);

        return ApiResponse::success(
            LowStockEventResource::collection($alerts),
            'Active low stock alerts retrieved successfully'
        );
    }
}
