<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\TogglePlatformRequest;
use App\Http\Resources\PlatformCollection;
use App\Http\Resources\PlatformResource;
use App\Models\Platform;
use App\Services\PlatformService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function __construct(
        private PlatformService $platformService
    ) {}

    /**
     * List all platforms with user's activation status.
     */
    public function index(Request $request): PlatformCollection
    {
        $platforms = $this->platformService->getAllWithUserStatus($request->user());

        return new PlatformCollection($platforms);
    }

    /**
     * Toggle platform activation for the authenticated user.
     */
    public function toggle(TogglePlatformRequest $request, Platform $platform): JsonResponse
    {
        $result = $this->platformService->toggleForUser($request->user(), $platform);

        $platform->is_active_for_user = $result['is_active'];

        $message = $result['is_active']
          ? "Platform '{$platform->name}' has been activated."
          : "Platform '{$platform->name}' has been deactivated.";

        return $this->resourceResponse(new PlatformResource($platform), $message);
    }
}
