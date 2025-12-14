<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlatformType;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Platform>
 */
class PlatformFactory extends Factory
{
  protected $model = Platform::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $type = $this->faker->randomElement(PlatformType::cases());

    return [
      'name' => $type->label(),
      'type' => $type->value,
      'character_limit' => $type->characterLimit(),
      'is_active' => true,
    ];
  }

  /**
   * Indicate that the platform is inactive.
   */
  public function inactive(): static
  {
    return $this->state(fn(array $attributes) => [
      'is_active' => false,
    ]);
  }

  /**
   * Create an X (Twitter) platform.
   */
  public function x(): static
  {
    return $this->state(fn(array $attributes) => [
      'name' => 'X (Twitter)',
      'type' => PlatformType::X->value,
      'character_limit' => 280,
    ]);
  }

  /**
   * Create an Instagram platform.
   */
  public function instagram(): static
  {
    return $this->state(fn(array $attributes) => [
      'name' => 'Instagram',
      'type' => PlatformType::INSTAGRAM->value,
      'character_limit' => 2200,
    ]);
  }

  /**
   * Create a LinkedIn platform.
   */
  public function linkedin(): static
  {
    return $this->state(fn(array $attributes) => [
      'name' => 'LinkedIn',
      'type' => PlatformType::LINKEDIN->value,
      'character_limit' => 3000,
    ]);
  }

  /**
   * Create a Facebook platform.
   */
  public function facebook(): static
  {
    return $this->state(fn(array $attributes) => [
      'name' => 'Facebook',
      'type' => PlatformType::FACEBOOK->value,
      'character_limit' => 63206,
    ]);
  }
}
