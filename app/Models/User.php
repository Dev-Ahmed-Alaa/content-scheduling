<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, HasApiTokens;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  /**
   * Get the posts for the user.
   */
  public function posts(): HasMany
  {
    return $this->hasMany(Post::class);
  }

  /**
   * Get the platforms activated for the user.
   */
  public function platforms(): BelongsToMany
  {
    return $this->belongsToMany(Platform::class, 'user_platform')
      ->withPivot('is_active')
      ->withTimestamps();
  }

  /**
   * Get only active platforms for the user.
   */
  public function activePlatforms(): BelongsToMany
  {
    return $this->platforms()->wherePivot('is_active', true);
  }

  /**
   * Check if user has a specific platform activated.
   */
  public function hasPlatformActive(int $platformId): bool
  {
    return $this->activePlatforms()->where('platforms.id', $platformId)->exists();
  }
}
