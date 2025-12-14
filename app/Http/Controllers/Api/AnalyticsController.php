<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    /**
     * Get overview analytics.
     */
    public function overview(Request $request): JsonResponse
    {
        $overview = $this->analyticsService->getOverview($request->user());

        return $this->success($overview);
    }

    /**
     * Get platform-specific analytics.
     */
    public function platforms(Request $request): JsonResponse
    {
        $stats = $this->analyticsService->getPlatformStats($request->user());

        return $this->success($stats);
    }

    /**
     * Get timeline analytics.
     */
    public function timeline(Request $request): JsonResponse
    {
        $from = $request->query('from') ?? now()->subDays(30)->toDateString();
        $to = $request->query('to') ?? now()->toDateString();

        $timeline = $this->analyticsService->getTimeline(
            $request->user(),
            $from,
            $to
        );

        return response()->json([
            'data' => $timeline,
            'meta' => [
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }
}
