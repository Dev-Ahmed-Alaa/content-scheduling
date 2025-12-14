<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPlatform extends Model
{
  protected $table = 'user_platform';

  protected $fillable = [
    'user_id',
    'platform_id',
    'is_active',
  ];

  protected function casts(): array
  {
    return [
      'is_active' => 'boolean',
    ];
  }

  /**
   * Get the user.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the platform.
   */
  public function platform(): BelongsTo
  {
    return $this->belongsTo(Platform::class);
  }

  /**
   * Toggle the active status.
   */
  public function toggle(): void
  {
    $this->update(['is_active' => !$this->is_active]);
  }
}
