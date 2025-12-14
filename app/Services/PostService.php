<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\PostRepositoryInterface;
use App\Enums\PlatformStatus;
use App\Enums\PostStatus;
use App\Exceptions\ScheduleRateLimitExceededException;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PostService
{
    private const DAILY_SCHEDULE_LIMIT = 10;

    public function __construct(
        private PostRepositoryInterface $postRepository,
        private PlatformValidationService $platformValidationService,
        private PlatformService $platformService
    ) {}

    /**
     * Create a new post.
     */
    public function create(User $user, array $data): Post
    {
        // Validate platforms for user
        $this->validatePlatformsForUser($user, $data['platform_ids']);

        // Validate content for each platform
        $this->platformValidationService->validateContent($data['content'], $data['platform_ids']);

        // Check rate limit if scheduling
        if (isset($data['status']) && $data['status'] === PostStatus::SCHEDULED->value) {
            $this->checkRateLimit($user);
        }

        // Create the post
        $post = $this->postRepository->create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'image_url' => $data['image_url'] ?? null,
            'scheduled_time' => $data['scheduled_time'] ?? null,
            'status' => $data['status'] ?? PostStatus::DRAFT->value,
        ]);

        // Attach platforms
        $this->postRepository->attachPlatforms($post, $data['platform_ids']);

        return $post->load('platforms');
    }

    /**
     * Update an existing post.
     */
    public function update(Post $post, array $data): Post
    {
        // If platforms are being updated
        if (isset($data['platform_ids'])) {
            $user = $post->user;
            $this->validatePlatformsForUser($user, $data['platform_ids']);
            $content = $data['content'] ?? $post->content;
            $this->platformValidationService->validateContent($content, $data['platform_ids']);
        }

        // If content is being updated, validate against existing platforms
        if (isset($data['content']) && ! isset($data['platform_ids'])) {
            $platformIds = $post->platforms->pluck('id')->toArray();
            $this->platformValidationService->validateContent($data['content'], $platformIds);
        }

        // Check rate limit if changing to scheduled
        if (isset($data['status']) && $data['status'] === PostStatus::SCHEDULED->value) {
            if ($post->status !== PostStatus::SCHEDULED) {
                $this->checkRateLimit($post->user);
            }
        }

        // Update the post
        $updateData = array_filter([
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'scheduled_time' => $data['scheduled_time'] ?? null,
            'status' => $data['status'] ?? null,
        ], fn ($value) => $value !== null);

        $post = $this->postRepository->update($post, $updateData);

        // Update platforms if provided
        if (isset($data['platform_ids'])) {
            $this->postRepository->attachPlatforms($post, $data['platform_ids']);
        }

        return $post->load('platforms');
    }

    /**
     * Delete a post (soft delete).
     */
    public function delete(Post $post): bool
    {
        return $this->postRepository->delete($post);
    }

    /**
     * Get posts for a user with filters.
     */
    public function listForUser(
        User $user,
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $postStatus = $status ? PostStatus::from($status) : null;

        return $this->postRepository->getForUser(
            $user,
            $postStatus,
            $dateFrom,
            $dateTo,
            $perPage
        );
    }

    /**
     * Get a single post with platforms.
     */
    public function findWithPlatforms(int $id): ?Post
    {
        return $this->postRepository->findWithPlatforms($id);
    }

    /**
     * Get posts due for publishing.
     */
    public function getDueForPublishing(): Collection
    {
        return $this->postRepository->getDueForPublishing();
    }

    /**
     * Mark post as published.
     */
    public function markAsPublished(Post $post): Post
    {
        return $this->postRepository->update($post, [
            'status' => PostStatus::PUBLISHED->value,
            'published_at' => now(),
        ]);
    }

    /**
     * Update platform status for a post.
     */
    public function updatePlatformStatus(
        Post $post,
        int $platformId,
        PlatformStatus $status,
        ?string $errorMessage = null
    ): void {
        $this->postRepository->updatePlatformStatus(
            $post,
            $platformId,
            $status->value,
            $errorMessage
        );
    }

    /**
     * Get remaining schedule quota for today.
     */
    public function getRemainingQuota(User $user, ?Carbon $date = null): int
    {
        $date = $date ?? Carbon::today();
        $usedCount = $this->postRepository->getScheduledCountForDate($user, $date);

        return max(0, self::DAILY_SCHEDULE_LIMIT - $usedCount);
    }

    /**
     * Get rate limit metadata for API responses.
     */
    public function getRateLimitMeta(User $user): array
    {
        return [
            'limit' => self::DAILY_SCHEDULE_LIMIT,
            'scheduled_posts_remaining' => $this->getRemainingQuota($user),
            'resets_at' => now()->endOfDay()->toIso8601String(),
        ];
    }

    /**
     * Check if user has exceeded the daily schedule limit.
     */
    private function checkRateLimit(User $user): void
    {
        $remainingQuota = $this->getRemainingQuota($user, Carbon::today());

        if ($remainingQuota <= 0) {
            throw new ScheduleRateLimitExceededException(
                'You have reached the maximum of '.self::DAILY_SCHEDULE_LIMIT.
                    ' scheduled posts for today. Try again tomorrow.'
            );
        }
    }

    /**
     * Validate that user has activated the selected platforms.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validatePlatformsForUser(User $user, array $platformIds): void
    {
        $inactivePlatforms = [];

        foreach ($platformIds as $platformId) {
            if (! $this->platformService->isActiveForUser($user, $platformId)) {
                $inactivePlatforms[] = $platformId;
            }
        }

        if (! empty($inactivePlatforms)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'platform_ids' => 'The following platforms are not activated for your account: '.implode(', ', $inactivePlatforms),
            ]);
        }
    }
}
