<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\PlatformStatus;
use App\Enums\PostStatus;
use App\Models\Platform;
use App\Models\Post;
use App\Services\PublisherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublishPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Post $post,
        public Platform $platform
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PublisherService $publisherService): void
    {
        Log::info('Publishing post to platform', [
            'post_id' => $this->post->id,
            'platform_id' => $this->platform->id,
            'platform_type' => $this->platform->type->value,
            'attempt' => $this->attempts(),
        ]);

        // Publish to platform
        $result = $publisherService->publish($this->post, $this->platform);

        // Update platform status
        DB::transaction(function () use ($result) {
            if ($result['success']) {
                $this->post->platforms()->updateExistingPivot($this->platform->id, [
                    'platform_status' => PlatformStatus::PUBLISHED->value,
                    'published_at' => now(),
                    'error_message' => null,
                ]);
            } else {
                $this->post->platforms()->updateExistingPivot($this->platform->id, [
                    'platform_status' => PlatformStatus::FAILED->value,
                    'error_message' => $result['message'],
                ]);
            }

            // Check if all platforms are processed
            $this->checkAndUpdatePostStatus();
        });
    }

    /**
     * Check if all platforms are processed and update post status accordingly.
     */
    private function checkAndUpdatePostStatus(): void
    {
        $this->post->refresh();

        $pendingCount = $this->post->platforms()
            ->wherePivot('platform_status', PlatformStatus::PENDING->value)
            ->count();

        // If there are still pending platforms, don't update the post status
        if ($pendingCount > 0) {
            return;
        }

        // All platforms processed - update post status
        $this->post->update([
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        Log::info('Post publishing completed', [
            'post_id' => $this->post->id,
            'status' => $this->post->status->value,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Post publishing job failed', [
            'post_id' => $this->post->id,
            'platform_id' => $this->platform->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Mark platform as failed after all retries exhausted
        $this->post->platforms()->updateExistingPivot($this->platform->id, [
            'platform_status' => PlatformStatus::FAILED->value,
            'error_message' => 'Publishing failed after '.$this->attempts().' attempts: '.$exception->getMessage(),
        ]);

        // Check and update post status
        $this->checkAndUpdatePostStatus();
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'publish-post',
            'post:'.$this->post->id,
            'platform:'.$this->platform->id,
        ];
    }
}
