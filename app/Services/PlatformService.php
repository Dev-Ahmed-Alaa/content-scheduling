<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\PlatformRepositoryInterface;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Support\Collection;

class PlatformService
{
    public function __construct(
        private PlatformRepositoryInterface $platformRepository
    ) {}

    /**
     * Get all platforms with user's activation status.
     */
    public function getAllWithUserStatus(User $user): Collection
    {
        return $this->platformRepository->getAllWithUserStatus($user);
    }

    /**
     * Get all active platforms.
     */
    public function getActivePlatforms(): Collection
    {
        return $this->platformRepository->getActive();
    }

    /**
     * Get user's active platforms.
     */
    public function getUserActivePlatforms(User $user): Collection
    {
        return $this->platformRepository->getUserActivePlatforms($user);
    }

    /**
     * Toggle platform activation for a user.
     */
    public function toggleForUser(User $user, Platform $platform): array
    {
        $isActive = $this->platformRepository->toggleForUser($user, $platform);

        return [
            'platform' => $platform,
            'is_active' => $isActive,
        ];
    }

    /**
     * Check if platform is active for user.
     */
    public function isActiveForUser(User $user, int $platformId): bool
    {
        return $this->platformRepository->isActiveForUser($user, $platformId);
    }

    /**
     * Find platform by ID.
     */
    public function find(int $id): ?Platform
    {
        return $this->platformRepository->find($id);
    }

    /**
     * Find platform by type.
     */
    public function findByType(string $type): ?Platform
    {
        return $this->platformRepository->findByType($type);
    }
}
