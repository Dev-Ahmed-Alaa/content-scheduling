<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        return $this->resourceResponse(new UserResource($request->user()));
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return $this->resourceResponse(
            new UserResource($user),
            'Profile updated successfully.'
        );
    }
}
