<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\PostRepositoryInterface;
use App\Enums\PlatformStatus;
use App\Enums\PostStatus;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {}

    /**
     * Get overview analytics for a user.
     */
    public function getOverview(User $user): array
    {
        $cacheKey = "analytics:overview:{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $posts = DB::table('posts')
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalPosts = array_sum($posts);

            // Get failed platform count
            $failedPlatforms = DB::table('post_platform')
                ->join('posts', 'posts.id', '=', 'post_platform.post_id')
                ->where('posts.user_id', $user->id)
                ->where('post_platform.platform_status', PlatformStatus::FAILED->value)
                ->count();

            return [
                'total_posts' => $totalPosts,
                'drafts' => $posts[PostStatus::DRAFT->value] ?? 0,
                'scheduled' => $posts[PostStatus::SCHEDULED->value] ?? 0,
                'published' => $posts[PostStatus::PUBLISHED->value] ?? 0,
                'failed_platforms' => $failedPlatforms,
            ];
        });
    }

    /**
     * Get platform analytics for a user.
     */
    public function getPlatformStats(User $user): Collection
    {
        $cacheKey = "analytics:platforms:{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $stats = DB::table('post_platform')
                ->join('posts', 'posts.id', '=', 'post_platform.post_id')
                ->join('platforms', 'platforms.id', '=', 'post_platform.platform_id')
                ->where('posts.user_id', $user->id)
                ->whereNull('posts.deleted_at')
                ->selectRaw('
                    platforms.id,
                    platforms.name,
                    platforms.type,
                    COUNT(*) as total_posts,
                    SUM(CASE WHEN post_platform.platform_status = ? THEN 1 ELSE 0 END) as published_count,
                    SUM(CASE WHEN post_platform.platform_status = ? THEN 1 ELSE 0 END) as failed_count,
                    SUM(CASE WHEN post_platform.platform_status = ? THEN 1 ELSE 0 END) as pending_count
                ', [
                    PlatformStatus::PUBLISHED->value,
                    PlatformStatus::FAILED->value,
                    PlatformStatus::PENDING->value,
                ])
                ->groupBy('platforms.id', 'platforms.name', 'platforms.type')
                ->get();

            return $stats->map(function ($stat) {
                $processedCount = $stat->published_count + $stat->failed_count;
                $successRate = $processedCount > 0
                  ? round(($stat->published_count / $processedCount) * 100, 2)
                  : 0;

                return [
                    'platform_id' => $stat->id,
                    'platform_name' => $stat->name,
                    'platform_type' => $stat->type,
                    'total_posts' => $stat->total_posts,
                    'published_count' => $stat->published_count,
                    'failed_count' => $stat->failed_count,
                    'pending_count' => $stat->pending_count,
                    'success_rate' => $successRate,
                ];
            });
        });
    }

    /**
     * Get timeline analytics for a user.
     */
    public function getTimeline(User $user, ?string $from = null, ?string $to = null): Collection
    {
        $startDate = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $endDate = $to ? Carbon::parse($to) : Carbon::now();

        $cacheKey = "analytics:timeline:{$user->id}:{$startDate->toDateString()}:{$endDate->toDateString()}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $startDate, $endDate) {
            // Get scheduled posts per day
            $scheduledData = DB::table('posts')
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->whereNotNull('scheduled_time')
                ->whereBetween('scheduled_time', [$startDate, $endDate])
                ->selectRaw('DATE(scheduled_time) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            // Get published posts per day
            $publishedData = DB::table('posts')
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->where('status', PostStatus::PUBLISHED->value)
                ->whereNotNull('published_at')
                ->whereBetween('published_at', [$startDate, $endDate])
                ->selectRaw('DATE(published_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            // Build timeline with zero-fill
            $timeline = collect();
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dateString = $date->toDateString();
                $timeline->push([
                    'date' => $dateString,
                    'scheduled' => $scheduledData[$dateString] ?? 0,
                    'published' => $publishedData[$dateString] ?? 0,
                ]);
            }

            return $timeline;
        });
    }

    /**
     * Clear analytics cache for a user.
     */
    public function clearCache(User $user): void
    {
        Cache::forget("analytics:overview:{$user->id}");
        Cache::forget("analytics:platforms:{$user->id}");
        // Timeline cache will expire naturally due to date-based keys
    }
}
