<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\PlatformRepositoryInterface;
use App\Contracts\Repositories\PostRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\PlatformType;
use App\Repositories\EloquentPlatformRepository;
use App\Repositories\EloquentPostRepository;
use App\Repositories\EloquentUserRepository;
use App\Validators\Platforms\FacebookValidator;
use App\Validators\Platforms\InstagramValidator;
use App\Validators\Platforms\LinkedInValidator;
use App\Validators\Platforms\XValidator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All platform validators mapped by platform type.
     */
    private array $platformValidators = [
        PlatformType::X->value => XValidator::class,
        PlatformType::INSTAGRAM->value => InstagramValidator::class,
        PlatformType::LINKEDIN->value => LinkedInValidator::class,
        PlatformType::FACEBOOK->value => FacebookValidator::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerPlatformValidators();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
    }

    /**
     * Register repository bindings.
     */
    private function registerRepositories(): void
    {
        $this->app->bind(PostRepositoryInterface::class, EloquentPostRepository::class);
        $this->app->bind(PlatformRepositoryInterface::class, EloquentPlatformRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }

    private function registerPlatformValidators(): void
    {
        foreach ($this->platformValidators as $type => $validator) {
            $this->app->bind("platform.validator.{$type}", $validator);
        }
    }
}
