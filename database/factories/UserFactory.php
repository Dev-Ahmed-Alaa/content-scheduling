<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a user with a specific email.
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }

    /**
     * Create a user with a specific password.
     */
    public function withPassword(string $password): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Create a test user with standard credentials.
     */
    public function testUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    /**
     * Create a demo user.
     */
    public function demoUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    /**
     * Create a power user for rate limit testing.
     */
    public function powerUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Power User',
            'email' => 'power@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    /**
     * Configure the user to have all platforms activated after creation.
     */
    public function withAllPlatformsActive(): static
    {
        return $this->afterCreating(function (User $user) {
            $platforms = \App\Models\Platform::all();
            foreach ($platforms as $platform) {
                $user->platforms()->attach($platform->id, ['is_active' => true]);
            }
        });
    }

    /**
     * Configure the user to have specific platforms activated after creation.
     *
     * @param  array<string>  $platformTypes  Array of platform types (e.g., ['x', 'instagram'])
     */
    public function withPlatformsActive(array $platformTypes): static
    {
        return $this->afterCreating(function (User $user) use ($platformTypes) {
            $platforms = \App\Models\Platform::whereIn('type', $platformTypes)->get();
            foreach ($platforms as $platform) {
                $user->platforms()->attach($platform->id, ['is_active' => true]);
            }
        });
    }
}
