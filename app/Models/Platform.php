<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlatformType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Platform extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'type',
    'character_limit',
    'is_active',
  ];

  protected function casts(): array
  {
    return [
      'type' => PlatformType::class,
      'character_limit' => 'integer',
      'is_active' => 'boolean',
    ];
  }

  /**
   * Get all posts for this platform.
   */
  public function posts(): BelongsToMany
  {
    return $this->belongsToMany(Post::class, 'post_platform')
      ->withPivot(['platform_status', 'published_at', 'error_message'])
      ->withTimestamps();
  }

  /**
   * Get all users who have activated this platform.
   */
  public function users(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'user_platform')
      ->withPivot('is_active')
      ->withTimestamps();
  }

  /**
   * Scope to get only active platforms.
   */
  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  /**
   * Get character limit based on platform type.
   */
  public function getCharacterLimitAttribute($value): int
  {
    return $value ?? $this->type?->characterLimit() ?? 280;
  }
}
