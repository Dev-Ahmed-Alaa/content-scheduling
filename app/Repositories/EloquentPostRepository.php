<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PostRepositoryInterface;
use App\Enums\PlatformStatus;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentPostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function getForUser(
        User $user,
        ?PostStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model
            ->forUser($user->id)
            ->with('platforms')
            ->latest('created_at');

        if ($status) {
            $query->status($status);
        }

        if ($dateFrom || $dateTo) {
            $query->dateRange($dateFrom, $dateTo);
        }

        return $query->paginate($perPage);
    }

    public function getDueForPublishing(): Collection
    {
        return $this->model
            ->dueForPublishing()
            ->with('platforms')
            ->get();
    }

    public function getScheduledCountForDate(User $user, Carbon $date): int
    {
        return $this->model
            ->forUser($user->id)
            ->whereIn('status', [PostStatus::SCHEDULED, PostStatus::PUBLISHED])
            ->whereDate('created_at', $date->toDateString())
            ->count();
    }

    public function attachPlatforms(Post $post, array $platformIds): void
    {
        $syncData = [];
        foreach ($platformIds as $platformId) {
            $syncData[$platformId] = [
                'platform_status' => PlatformStatus::PENDING->value,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $post->platforms()->sync($syncData);
    }

    public function updatePlatformStatus(
        Post $post,
        int $platformId,
        string $status,
        ?string $errorMessage = null
    ): void {
        $updateData = [
            'platform_status' => $status,
            'updated_at' => now(),
        ];

        if ($status === PlatformStatus::PUBLISHED->value) {
            $updateData['published_at'] = now();
        }

        if ($errorMessage !== null) {
            $updateData['error_message'] = $errorMessage;
        }

        $post->platforms()->updateExistingPivot($platformId, $updateData);
    }

    public function findWithPlatforms(int $id): ?Post
    {
        return $this->model->with('platforms')->find($id);
    }

    /**
     * Override create to handle post creation.
     */
    public function create(array $data): Post
    {
        return $this->model->create($data);
    }
}
