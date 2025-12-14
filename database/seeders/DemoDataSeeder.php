<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlatformStatus;
use App\Enums\PostStatus;
use App\Models\Platform;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates comprehensive demo data for testing all features:
     * - Posts in various statuses (draft, scheduled, published)
     * - Posts with multiple platform combinations
     * - Platform statuses (pending, published, failed)
     * - Historical data for analytics
     * - Rate limit testing data
     */
    public function run(): void
    {
        $platforms = Platform::all()->keyBy('type');
        $users = User::all()->keyBy('email');

        // Get specific users
        $testUser = $users['test@example.com'] ?? User::first();
        $demoUser = $users['demo@example.com'] ?? User::skip(1)->first();
        $powerUser = $users['power@example.com'] ?? User::skip(2)->first();

        $this->command->info('Creating demo posts for Test User...');
        $this->createTestUserPosts($testUser, $platforms);

        $this->command->info('Creating demo posts for Demo User...');
        $this->createDemoUserPosts($demoUser, $platforms);

        $this->command->info('Creating demo posts for Power User (rate limit testing)...');
        $this->createPowerUserPosts($powerUser, $platforms);

        $this->command->info('Creating historical data for analytics...');
        $this->createHistoricalData($demoUser, $platforms);

        $this->command->newLine();
        $this->command->info('Demo data seeding completed!');
    }

    /**
     * Create posts for Test User.
     * Scenarios: Basic CRUD operations testing
     */
    private function createTestUserPosts(User $user, $platforms): void
    {
        // 1. Draft post - for testing draft functionality
        $draftPost = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'My First Draft Post',
            'content' => 'This is a draft post that has not been scheduled yet. Perfect for testing the draft to scheduled workflow.',
            'status' => PostStatus::DRAFT,
            'scheduled_time' => null,
        ]);
        $this->attachPlatforms($draftPost, [
            $platforms['x']->id => PlatformStatus::PENDING,
            $platforms['instagram']->id => PlatformStatus::PENDING,
        ]);

        // 2. Scheduled post (future) - for testing scheduled posts
        $scheduledPost = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Scheduled Marketing Campaign',
            'content' => 'This post is scheduled for future publication. Check out our amazing product launch! 🚀 #launch #marketing',
            'image_url' => 'https://picsum.photos/800/600',
            'status' => PostStatus::SCHEDULED,
            'scheduled_time' => Carbon::now()->addDays(2)->setHour(10)->setMinute(0),
        ]);
        $this->attachPlatforms($scheduledPost, [
            $platforms['x']->id => PlatformStatus::PENDING,
            $platforms['instagram']->id => PlatformStatus::PENDING,
        ]);

        // 3. Scheduled post (due soon) - for testing the publish command
        $dueSoonPost = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Post Due for Publishing',
            'content' => 'This post is scheduled for the past and should be picked up by the publish command.',
            'status' => PostStatus::SCHEDULED,
            'scheduled_time' => Carbon::now()->subMinutes(5),
        ]);
        $this->attachPlatforms($dueSoonPost, [
            $platforms['x']->id => PlatformStatus::PENDING,
        ]);

        // 4. Published post - for testing published posts display
        $publishedPost = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Successfully Published Announcement',
            'content' => 'Great news! Our latest feature is now live. Check it out and let us know what you think! 💡',
            'image_url' => 'https://picsum.photos/800/400',
            'status' => PostStatus::PUBLISHED,
            'scheduled_time' => Carbon::now()->subDays(3),
            'published_at' => Carbon::now()->subDays(3),
        ]);
        $this->attachPlatforms($publishedPost, [
            $platforms['x']->id => PlatformStatus::PUBLISHED,
            $platforms['instagram']->id => PlatformStatus::PUBLISHED,
        ], true);

        // 5. Post with mixed platform statuses - for testing partial success
        $mixedPost = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Partial Success Post',
            'content' => 'This post succeeded on some platforms but failed on others.',
            'status' => PostStatus::PUBLISHED,
            'scheduled_time' => Carbon::now()->subDays(1),
            'published_at' => Carbon::now()->subDays(1),
        ]);
        // Attach X as published
        $mixedPost->platforms()->attach($platforms['x']->id, [
            'platform_status' => PlatformStatus::PUBLISHED->value,
            'published_at' => Carbon::now()->subDays(1),
        ]);
        // Attach Instagram as failed
        $mixedPost->platforms()->attach($platforms['instagram']->id, [
            'platform_status' => PlatformStatus::FAILED->value,
            'error_message' => 'Rate limit exceeded. Please try again later.',
        ]);

        $this->command->info('  Created 5 posts for Test User');
    }

    /**
     * Create posts for Demo User.
     * Scenarios: Multi-platform posting, analytics data
     */
    private function createDemoUserPosts(User $user, $platforms): void
    {
        // Multiple published posts across all platforms for analytics
        $publishedContents = [
            [
                'title' => 'Company Update Q4',
                'content' => 'Exciting Q4 results! We\'ve grown 150% this quarter. Thank you to all our amazing customers and team members! 📈',
            ],
            [
                'title' => 'Product Feature Release',
                'content' => 'Introducing our new dashboard! Sleek design, faster performance, and better insights. Update your app now! 🎨',
            ],
            [
                'title' => 'Team Milestone Celebration',
                'content' => 'Celebrating 5 years of innovation! Here\'s to the next 5 years of building amazing products together. 🎉',
            ],
            [
                'title' => 'Customer Success Story',
                'content' => 'How Company ABC increased their efficiency by 40% using our platform. Read the full case study! 📖',
            ],
            [
                'title' => 'Industry Insights',
                'content' => 'The future of social media marketing: AI, automation, and authentic engagement. What do you think? 🤖',
            ],
        ];

        foreach ($publishedContents as $index => $content) {
            $daysAgo = ($index + 1) * 5;
            $post = Post::factory()->create([
                'user_id' => $user->id,
                'title' => $content['title'],
                'content' => $content['content'],
                'image_url' => 'https://picsum.photos/800/'.(400 + $index * 50),
                'status' => PostStatus::PUBLISHED,
                'scheduled_time' => Carbon::now()->subDays($daysAgo),
                'published_at' => Carbon::now()->subDays($daysAgo),
            ]);

            // Attach all 4 platforms with varying statuses
            $platformStatuses = $this->getRandomPlatformStatuses($platforms);
            foreach ($platformStatuses as $platformId => $statusData) {
                $post->platforms()->attach($platformId, $statusData);
            }
        }

        // Scheduled posts for different dates
        for ($i = 1; $i <= 3; $i++) {
            $post = Post::factory()->create([
                'user_id' => $user->id,
                'title' => "Upcoming Campaign #{$i}",
                'content' => "This is scheduled content #{$i} ready to go live. Stay tuned for more updates!",
                'status' => PostStatus::SCHEDULED,
                'scheduled_time' => Carbon::now()->addDays($i * 2)->setHour(14)->setMinute(0),
            ]);

            $this->attachPlatforms($post, [
                $platforms['x']->id => PlatformStatus::PENDING,
                $platforms['linkedin']->id => PlatformStatus::PENDING,
                $platforms['facebook']->id => PlatformStatus::PENDING,
            ]);
        }

        // Draft posts
        for ($i = 1; $i <= 2; $i++) {
            $post = Post::factory()->create([
                'user_id' => $user->id,
                'title' => "Draft Idea #{$i}",
                'content' => "This is a work in progress draft #{$i}. Need to finalize the content before scheduling.",
                'status' => PostStatus::DRAFT,
                'scheduled_time' => null,
            ]);

            $this->attachPlatforms($post, [
                $platforms['instagram']->id => PlatformStatus::PENDING,
            ]);
        }

        $this->command->info('  Created 10 posts for Demo User');
    }

    /**
     * Create posts for Power User.
     * Scenarios: Rate limit testing (close to daily limit)
     */
    private function createPowerUserPosts(User $user, $platforms): void
    {
        // Create 8 scheduled posts for today to test rate limiting
        // (User will have 2 remaining of their 10 daily limit)
        for ($i = 1; $i <= 8; $i++) {
            $post = Post::factory()->create([
                'user_id' => $user->id,
                'title' => "Scheduled Post #{$i} for Today",
                'content' => "This is scheduled post #{$i} created today to test rate limiting. Only 2 more posts can be scheduled!",
                'status' => PostStatus::SCHEDULED,
                'scheduled_time' => Carbon::now()->addHours($i + 1),
                'created_at' => Carbon::today()->addHours($i),
                'updated_at' => Carbon::today()->addHours($i),
            ]);

            $this->attachPlatforms($post, [
                $platforms['linkedin']->id => PlatformStatus::PENDING,
                $platforms['facebook']->id => PlatformStatus::PENDING,
            ]);
        }

        // Some historical published posts
        for ($i = 1; $i <= 5; $i++) {
            $daysAgo = $i * 3;
            $post = Post::factory()->create([
                'user_id' => $user->id,
                'title' => "Historical Post #{$i}",
                'content' => "This is a historical post published {$daysAgo} days ago for analytics.",
                'status' => PostStatus::PUBLISHED,
                'scheduled_time' => Carbon::now()->subDays($daysAgo),
                'published_at' => Carbon::now()->subDays($daysAgo),
            ]);

            $this->attachPlatforms($post, [
                $platforms['linkedin']->id => PlatformStatus::PUBLISHED,
                $platforms['facebook']->id => PlatformStatus::PUBLISHED,
            ], true);
        }

        $this->command->info('  Created 13 posts for Power User (8 scheduled today for rate limit testing)');
    }

    /**
     * Create historical data for analytics testing.
     */
    private function createHistoricalData(User $user, $platforms): void
    {
        // Create posts spread across the last 30 days for timeline analytics
        for ($day = 1; $day <= 30; $day++) {
            // Skip some days to create realistic gaps
            if ($day % 7 === 0) {
                continue;
            }

            $postsPerDay = rand(0, 3);
            for ($p = 0; $p < $postsPerDay; $p++) {
                $date = Carbon::now()->subDays($day)->setHour(rand(8, 18))->setMinute(rand(0, 59));

                $post = Post::factory()->create([
                    'user_id' => $user->id,
                    'title' => "Analytics Post Day-{$day}-{$p}",
                    'content' => "Historical content for analytics testing. Created on day -{$day}.",
                    'status' => PostStatus::PUBLISHED,
                    'scheduled_time' => $date,
                    'published_at' => $date,
                    'created_at' => $date->copy()->subHour(),
                    'updated_at' => $date,
                ]);

                // Randomly assign platforms
                $platformsToAttach = $platforms->random(rand(1, 4));
                foreach ($platformsToAttach as $platform) {
                    // 80% success rate
                    $isSuccess = rand(1, 100) <= 80;
                    $post->platforms()->attach($platform->id, [
                        'platform_status' => $isSuccess ? PlatformStatus::PUBLISHED->value : PlatformStatus::FAILED->value,
                        'published_at' => $isSuccess ? $date : null,
                        'error_message' => $isSuccess ? null : 'Simulated failure for testing',
                    ]);
                }
            }
        }

        $this->command->info('  Created ~45 historical posts for analytics');
    }

    /**
     * Attach platforms to a post with given statuses.
     */
    private function attachPlatforms(Post $post, array $platformStatuses, bool $setPublishedAt = false): void
    {
        foreach ($platformStatuses as $platformId => $status) {
            $pivotData = [
                'platform_status' => $status instanceof PlatformStatus ? $status->value : $status,
            ];

            if ($setPublishedAt && $status === PlatformStatus::PUBLISHED) {
                $pivotData['published_at'] = $post->published_at ?? now();
            }

            $post->platforms()->attach($platformId, $pivotData);
        }
    }

    /**
     * Get random platform statuses for analytics variety.
     */
    private function getRandomPlatformStatuses($platforms): array
    {
        $statuses = [];

        foreach ($platforms as $platform) {
            // 70% published, 20% failed, 10% still pending (edge case)
            $rand = rand(1, 100);
            if ($rand <= 70) {
                $statuses[$platform->id] = [
                    'platform_status' => PlatformStatus::PUBLISHED->value,
                    'published_at' => Carbon::now()->subDays(rand(1, 10)),
                ];
            } elseif ($rand <= 90) {
                $errorMessages = [
                    'Rate limit exceeded. Please try again later.',
                    'Authentication failed. Please reconnect your account.',
                    'Content policy violation detected.',
                    'Network timeout. Unable to reach the platform.',
                ];
                $statuses[$platform->id] = [
                    'platform_status' => PlatformStatus::FAILED->value,
                    'error_message' => $errorMessages[array_rand($errorMessages)],
                ];
            } else {
                $statuses[$platform->id] = [
                    'platform_status' => PlatformStatus::PENDING->value,
                ];
            }
        }

        return $statuses;
    }
}
