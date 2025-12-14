<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlatformStatus;
use App\Enums\PostStatus;
use App\Models\Platform;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Create demo user
    $user = User::firstOrCreate(
      ['email' => 'demo@example.com'],
      [
        'name' => 'Demo User',
        'password' => Hash::make('password'),
      ]
    );

    $this->command->info("Demo user created: {$user->email}");

    // Get platforms
    $platforms = Platform::all();

    // Activate all platforms for demo user
    foreach ($platforms as $platform) {
      $user->platforms()->syncWithoutDetaching([
        $platform->id => ['is_active' => true],
      ]);
    }

    $this->command->info('All platforms activated for demo user.');

    // Create sample posts
    $this->createDraftPosts($user, $platforms);
    $this->createScheduledPosts($user, $platforms);
    $this->createPublishedPosts($user, $platforms);

    $this->command->info('Demo data seeded successfully.');
  }

  private function createDraftPosts(User $user, $platforms): void
  {
    $drafts = [
      [
        'title' => 'Draft: Product Launch Announcement',
        'content' => 'Exciting news! We are launching our new product next week. Stay tuned for more details. #ProductLaunch #Innovation',
      ],
      [
        'title' => 'Draft: Weekly Tips',
        'content' => 'Here are 5 tips to boost your productivity: 1. Plan your day 2. Take breaks 3. Stay hydrated 4. Exercise 5. Sleep well',
      ],
    ];

    foreach ($drafts as $draft) {
      $post = Post::create([
        'user_id' => $user->id,
        'title' => $draft['title'],
        'content' => $draft['content'],
        'status' => PostStatus::DRAFT,
      ]);

      // Attach random platforms
      $selectedPlatforms = $platforms->random(rand(1, 3))->pluck('id')->toArray();
      $post->platforms()->attach($selectedPlatforms, [
        'platform_status' => PlatformStatus::PENDING->value,
      ]);
    }

    $this->command->info('Created ' . count($drafts) . ' draft posts.');
  }

  private function createScheduledPosts(User $user, $platforms): void
  {
    $scheduled = [
      [
        'title' => 'Scheduled: Morning Motivation',
        'content' => 'Good morning everyone! Remember, every day is a new opportunity to grow. #MondayMotivation #Growth',
        'scheduled_time' => now()->addHours(2),
      ],
      [
        'title' => 'Scheduled: Feature Highlight',
        'content' => 'Did you know our platform supports scheduling posts to multiple platforms at once? Try it out today! #ContentScheduling',
        'scheduled_time' => now()->addDays(1),
      ],
      [
        'title' => 'Scheduled: Weekend Reminder',
        'content' => 'Weekend is coming! Plan your content calendar for next week to stay ahead. #ContentPlanning #SocialMedia',
        'scheduled_time' => now()->addDays(3),
      ],
    ];

    foreach ($scheduled as $item) {
      $post = Post::create([
        'user_id' => $user->id,
        'title' => $item['title'],
        'content' => $item['content'],
        'scheduled_time' => $item['scheduled_time'],
        'status' => PostStatus::SCHEDULED,
      ]);

      // Attach random platforms
      $selectedPlatforms = $platforms->random(rand(2, 4))->pluck('id')->toArray();
      $post->platforms()->attach($selectedPlatforms, [
        'platform_status' => PlatformStatus::PENDING->value,
      ]);
    }

    $this->command->info('Created ' . count($scheduled) . ' scheduled posts.');
  }

  private function createPublishedPosts(User $user, $platforms): void
  {
    $published = [
      [
        'title' => 'Published: Welcome Post',
        'content' => 'Hello world! We are excited to announce our presence on social media. Follow us for updates! #Hello #Welcome',
        'published_at' => now()->subDays(7),
      ],
      [
        'title' => 'Published: Company Update',
        'content' => 'We have reached 1000 users! Thank you for your support. Here\'s to many more milestones! #Milestone #ThankYou',
        'published_at' => now()->subDays(3),
      ],
    ];

    foreach ($published as $item) {
      $post = Post::create([
        'user_id' => $user->id,
        'title' => $item['title'],
        'content' => $item['content'],
        'scheduled_time' => $item['published_at'],
        'published_at' => $item['published_at'],
        'status' => PostStatus::PUBLISHED,
      ]);

      // Attach platforms with published status
      $selectedPlatforms = $platforms->random(rand(2, 4))->pluck('id')->toArray();
      foreach ($selectedPlatforms as $platformId) {
        $post->platforms()->attach($platformId, [
          'platform_status' => PlatformStatus::PUBLISHED->value,
          'published_at' => $item['published_at'],
        ]);
      }
    }

    $this->command->info('Created ' . count($published) . ' published posts.');
  }
}
