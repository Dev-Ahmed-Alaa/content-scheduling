<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Platform;
use App\Models\User;
use App\Models\UserPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for creating UserPlatform (pivot) records.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPlatform>
 */
class UserPlatformFactory extends Factory
{
    protected $model = UserPlatform::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'platform_id' => Platform::factory(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the platform is active for the user.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the platform is inactive for the user.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Assign to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
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
}
