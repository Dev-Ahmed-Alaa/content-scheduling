<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
  protected $model = Post::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'user_id' => User::factory(),
      'title' => $this->faker->sentence(4),
      'content' => $this->faker->paragraph(2),
      'image_url' => $this->faker->optional(0.3)->imageUrl(),
      'scheduled_time' => null,
      'status' => PostStatus::DRAFT,
      'published_at' => null,
    ];
  }

  /**
   * Indicate that the post is a draft.
   */
  public function draft(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => PostStatus::DRAFT,
      'scheduled_time' => null,
      'published_at' => null,
    ]);
  }

  /**
   * Indicate that the post is scheduled.
   */
  public function scheduled(?string $scheduledTime = null): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => PostStatus::SCHEDULED,
      'scheduled_time' => $scheduledTime ?? $this->faker->dateTimeBetween('+1 hour', '+7 days'),
      'published_at' => null,
    ]);
  }

  /**
   * Indicate that the post is published.
   */
  public function published(?string $publishedAt = null): static
  {
    $publishedAt = $publishedAt ?? $this->faker->dateTimeBetween('-30 days', '-1 hour');

    return $this->state(fn(array $attributes) => [
      'status' => PostStatus::PUBLISHED,
      'scheduled_time' => $publishedAt,
      'published_at' => $publishedAt,
    ]);
  }

  /**
   * Create a post with short content (for X/Twitter).
   */
  public function shortContent(): static
  {
    return $this->state(fn(array $attributes) => [
      'content' => $this->faker->text(200),
    ]);
  }

  /**
   * Create a post with long content.
   */
  public function longContent(): static
  {
    return $this->state(fn(array $attributes) => [
      'content' => $this->faker->paragraphs(5, true),
    ]);
  }

  /**
   * Assign to a specific user.
   */
  public function forUser(User $user): static
  {
    return $this->state(fn(array $attributes) => [
      'user_id' => $user->id,
    ]);
  }

  /**
   * Set a specific scheduled time.
   */
  public function scheduledAt(string $dateTime): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => PostStatus::SCHEDULED,
      'scheduled_time' => $dateTime,
    ]);
  }
}
