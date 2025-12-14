<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PlatformStatus;
use App\Models\Platform;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class PublisherService
{
    /**
     * Mock publish to a platform.
     * In a real application, this would integrate with platform APIs.
     */
    public function publish(Post $post, Platform $platform): array
    {
        // Simulate API call delay
        usleep(random_int(100000, 500000));
        // Mock 80% success rate
        $success = random_int(1, 100) <= 80;

        if ($success) {
            Log::info('Post published successfully', [
                'post_id' => $post->id,
                'platform_id' => $platform->id,
                'platform_type' => $platform->type->value,
            ]);

            return [
                'success' => true,
                'message' => 'Post published successfully to '.$platform->name,
                'external_id' => 'mock_'.uniqid(),
            ];
        }

        $errorMessages = [
            'Rate limit exceeded. Please try again later.',
            'Authentication failed. Please reconnect your account.',
            'Content policy violation detected.',
            'Network timeout. Unable to reach the platform.',
            'Invalid media format.',
        ];

        $errorMessage = $errorMessages[array_rand($errorMessages)];

        Log::warning('Post publishing failed', [
            'post_id' => $post->id,
            'platform_id' => $platform->id,
            'platform_type' => $platform->type->value,
            'error' => $errorMessage,
        ]);

        return [
            'success' => false,
            'message' => $errorMessage,
            'external_id' => null,
        ];
    }

    /**
     * Check if all platforms for a post have been processed.
     */
    public function allPlatformsProcessed(Post $post): bool
    {
        return $post->platforms()
            ->wherePivot('platform_status', PlatformStatus::PENDING->value)
            ->doesntExist();
    }

    /**
     * Check if all platforms published successfully.
     */
    public function allPlatformsSucceeded(Post $post): bool
    {
        $post->refresh();

        return $post->platforms()
            ->wherePivotNot('platform_status', PlatformStatus::PUBLISHED->value)
            ->doesntExist();
    }

    /**
     * Get publishing summary for a post.
     */
    public function getPublishingSummary(Post $post): array
    {
        $platforms = $post->platforms()->get();

        $summary = [
            'total' => $platforms->count(),
            'published' => 0,
            'failed' => 0,
            'pending' => 0,
            'details' => [],
        ];

        foreach ($platforms as $platform) {
            $status = PlatformStatus::from($platform->pivot->platform_status);
            $statusKey = strtolower($status->value);
            $summary[$statusKey]++;

            $summary['details'][] = [
                'platform_id' => $platform->id,
                'platform_name' => $platform->name,
                'status' => $status->value,
                'published_at' => $platform->pivot->published_at,
                'error_message' => $platform->pivot->error_message,
            ];
        }

        return $summary;
    }
}
