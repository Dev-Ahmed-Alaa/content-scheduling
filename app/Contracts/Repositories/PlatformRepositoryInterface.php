<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Support\Collection;

interface PlatformRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all active platforms.
     */
    public function getActive(): Collection;

    /**
     * Get platforms with user's activation status.
     */
    public function getAllWithUserStatus(User $user): Collection;

    /**
     * Get user's active platforms.
     */
    public function getUserActivePlatforms(User $user): Collection;

    /**
     * Toggle platform activation for a user.
     */
    public function toggleForUser(User $user, Platform $platform): bool;

    /**
     * Check if user has platform activated.
     */
    public function isActiveForUser(User $user, int $platformId): bool;

    /**
     * Find platform by type.
     */
    public function findByType(string $type): ?Platform;

    /**
     * Find platforms by IDs.
     */
    public function findByIds(array $ids): Collection;
}
