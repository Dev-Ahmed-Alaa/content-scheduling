<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlatformStatus;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'image_url',
        'scheduled_time',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'scheduled_time' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the platforms for this post.
     */
    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class, 'post_platform')
            ->withPivot(['platform_status', 'published_at', 'error_message'])
            ->withTimestamps();
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, PostStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get posts due for publishing.
     */
    public function scopeDueForPublishing($query)
    {
        return $query->where('status', PostStatus::SCHEDULED)
            ->where('scheduled_time', '<=', now());
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, ?string $from = null, ?string $to = null)
    {
        if ($from) {
            $query->where('scheduled_time', '>=', $from);
        }
        if ($to) {
            $query->where('scheduled_time', '<=', $to);
        }
        return $query;
    }

    /**
     * Check if post can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->status !== PostStatus::PUBLISHED;
    }

    /**
     * Check if post can be scheduled.
     */
    public function canBeScheduled(): bool
    {
        return $this->status === PostStatus::DRAFT;
    }

    /**
     * Check if all platforms have been processed.
     */
    public function allPlatformsProcessed(): bool
    {
        return $this->platforms()
            ->wherePivotIn('platform_status', [PlatformStatus::PENDING->value])
            ->doesntExist();
    }

    /**
     * Check if all platforms published successfully.
     */
    public function allPlatformsSucceeded(): bool
    {
        return $this->platforms()
            ->wherePivotNot('platform_status', PlatformStatus::PUBLISHED->value)
            ->doesntExist();
    }

    /**
     * Get count of platforms by status.
     */
    public function getPlatformStatusCounts(): array
    {
        $counts = $this->platforms()
            ->selectRaw('pivot_platform_status as status, count(*) as count')
            ->groupBy('pivot_platform_status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'pending' => $counts[PlatformStatus::PENDING->value] ?? 0,
            'published' => $counts[PlatformStatus::PUBLISHED->value] ?? 0,
            'failed' => $counts[PlatformStatus::FAILED->value] ?? 0,
        ];
    }
}
