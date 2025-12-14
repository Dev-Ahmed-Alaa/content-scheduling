<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlatformStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostPlatform extends Model
{
  protected $table = 'post_platform';

  protected $fillable = [
    'post_id',
    'platform_id',
    'platform_status',
    'published_at',
    'error_message',
  ];

  protected function casts(): array
  {
    return [
      'platform_status' => PlatformStatus::class,
      'published_at' => 'datetime',
    ];
  }

  /**
   * Get the post.
   */
  public function post(): BelongsTo
  {
    return $this->belongsTo(Post::class);
  }

  /**
   * Get the platform.
   */
  public function platform(): BelongsTo
  {
    return $this->belongsTo(Platform::class);
  }

  /**
   * Check if this platform post is pending.
   */
  public function isPending(): bool
  {
    return $this->platform_status === PlatformStatus::PENDING;
  }

  /**
   * Check if this platform post has been processed.
   */
  public function isProcessed(): bool
  {
    return $this->platform_status->isTerminal();
  }

  /**
   * Mark as published.
   */
  public function markAsPublished(): void
  {
    $this->update([
      'platform_status' => PlatformStatus::PUBLISHED,
      'published_at' => now(),
      'error_message' => null,
    ]);
  }

  /**
   * Mark as failed.
   */
  public function markAsFailed(string $errorMessage): void
  {
    $this->update([
      'platform_status' => PlatformStatus::FAILED,
      'error_message' => $errorMessage,
    ]);
  }
}
