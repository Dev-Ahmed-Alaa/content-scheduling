<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PlatformRepositoryInterface;
use App\Models\Platform;
use App\Models\User;
use App\Models\UserPlatform;
use Illuminate\Support\Collection;

class EloquentPlatformRepository extends BaseRepository implements PlatformRepositoryInterface
{
    public function __construct(Platform $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function getAllWithUserStatus(User $user): Collection
    {
        return $this->model
            ->active()
            ->get()
            ->map(function (Platform $platform) use ($user) {
                $userPlatform = UserPlatform::where('user_id', $user->id)
                    ->where('platform_id', $platform->id)
                    ->first();

                $platform->is_active_for_user = $userPlatform?->is_active ?? false;

                return $platform;
            });
    }

    public function getUserActivePlatforms(User $user): Collection
    {
        return $user->activePlatforms()->get();
    }

    public function toggleForUser(User $user, Platform $platform): bool
    {
        $userPlatform = UserPlatform::firstOrCreate(
            [
                'user_id' => $user->id,
                'platform_id' => $platform->id,
            ],
            [
                'is_active' => false,
            ]
        );

        $newStatus = ! $userPlatform->is_active;
        $userPlatform->update(['is_active' => $newStatus]);

        return $newStatus;
    }

    public function isActiveForUser(User $user, int $platformId): bool
    {
        return UserPlatform::where('user_id', $user->id)
            ->where('platform_id', $platformId)
            ->where('is_active', true)
            ->exists();
    }

    public function findByType(string $type): ?Platform
    {
        return $this->model->where('type', $type)->first();
    }

    public function findByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get();
    }
}
