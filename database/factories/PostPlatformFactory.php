<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlatformStatus;
use App\Models\Platform;
use App\Models\Post;
use App\Models\PostPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for creating PostPlatform (pivot) records.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostPlatform>
 */
class PostPlatformFactory extends Factory
{
    protected $model = PostPlatform::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'platform_id' => Platform::factory(),
            'platform_status' => PlatformStatus::PENDING->value,
            'published_at' => null,
            'error_message' => null,
        ];
    }

    /**
     * Indicate that the platform status is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_status' => PlatformStatus::PENDING->value,
            'published_at' => null,
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the platform status is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_status' => PlatformStatus::PUBLISHED->value,
            'published_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the platform status is failed.
     */
    public function failed(?string $errorMessage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_status' => PlatformStatus::FAILED->value,
            'published_at' => null,
            'error_message' => $errorMessage ?? $this->faker->randomElement([
                'Rate limit exceeded. Please try again later.',
                'Authentication failed. Please reconnect your account.',
                'Content policy violation detected.',
                'Network timeout. Unable to reach the platform.',
                'Invalid media format.',
            ]),
        ]);
    }

    /**
     * Assign to a specific post.
     */
    public function forPost(Post $post): static
    {
        return $this->state(fn (array $attributes) => [
            'post_id' => $post->id,
        ]);
    }

    /**
     * Assign to a specific platform.
     */
    public function forPlatform(Platform $platform): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_id' => $platform->id,
        ]);
    }

    /**
     * Set a specific published date.
     */
    public function publishedAt(\DateTimeInterface|string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_status' => PlatformStatus::PUBLISHED->value,
            'published_at' => $date,
        ]);
    }
}
