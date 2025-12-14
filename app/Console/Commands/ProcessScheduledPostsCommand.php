<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PlatformStatus;
use App\Jobs\PublishPostJob;
use App\Services\PostService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessScheduledPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'posts:publish-due {--dry-run : Show what would be published without actually publishing}';

    /**
     * The console command description.
     */
    protected $description = 'Process and publish posts that are due for publishing';

    /**
     * Execute the console command.
     */
    public function handle(PostService $postService): int
    {
        $this->info('Checking for posts due for publishing...');

        $dueForPublishing = $postService->getDueForPublishing();

        if ($dueForPublishing->isEmpty()) {
            $this->info('No posts are due for publishing.');

            return self::SUCCESS;
        }

        $this->info("Found {$dueForPublishing->count()} post(s) due for publishing.");

        $isDryRun = $this->option('dry-run');

        foreach ($dueForPublishing as $post) {
            // Acquire lock to prevent duplicate processing
            $lockKey = "post-publishing:{$post->id}";

            $lock = Cache::lock($lockKey, 300); // 5 minutes

            if (! $lock->get()) {
                $this->warn("Post #{$post->id} is already being processed. Skipping.");
                Log::info('Post already being processed', ['post_id' => $post->id]);

                continue;
            }

            try {
                $this->processPost($post, $isDryRun);
            } finally {
                $lock->release();
            }
        }

        $this->info('Processing complete.');

        return self::SUCCESS;
    }

    /**
     * Process a single post for publishing.
     */
    private function processPost($post, bool $isDryRun): void
    {
        $platforms = $post->platforms()
            ->wherePivot('platform_status', PlatformStatus::PENDING->value)
            ->get();

        if ($platforms->isEmpty()) {
            $this->warn("Post #{$post->id} has no pending platforms. Skipping.");

            return;
        }

        $this->info("Processing Post #{$post->id}: \"{$post->title}\"");
        $this->info("  Scheduled for: {$post->scheduled_time}");
        $this->info('  Platforms: '.$platforms->pluck('name')->join(', '));

        if ($isDryRun) {
            $this->comment("  [DRY-RUN] Would dispatch {$platforms->count()} publishing job(s).");

            return;
        }

        foreach ($platforms as $platform) {
            $this->info("  Dispatching job for platform: {$platform->name}");

            PublishPostJob::dispatch($post, $platform);

            Log::info('Dispatched publish job', [
                'post_id' => $post->id,
                'platform_id' => $platform->id,
                'platform_name' => $platform->name,
            ]);
        }

        $this->info("  Dispatched {$platforms->count()} job(s) for Post #{$post->id}");
    }
}
