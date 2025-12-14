<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface extends RepositoryInterface
{
    /**
     * Get posts for a specific user with filters.
     */
    public function getForUser(
        User $user,
        ?PostStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 15
    ): LengthAwarePaginator;

    /**
     * Get posts that are due for publishing.
     */
    public function getDueForPublishing(): \Illuminate\Support\Collection;

    /**
     * Get scheduled posts count for a user on a specific date.
     */
    public function getScheduledCountForDate(User $user, Carbon $date): int;

    /**
     * Attach platforms to a post.
     */
    public function attachPlatforms(Post $post, array $platformIds): void;

    /**
     * Update platform status for a post.
     */
    public function updatePlatformStatus(Post $post, int $platformId, string $status, ?string $errorMessage = null): void;

    /**
     * Find post with platforms loaded.
     */
    public function findWithPlatforms(int $id): ?Post;
}
