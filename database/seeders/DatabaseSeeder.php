<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  use WithoutModelEvents;

  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // Seed platforms first (required)
    $this->call(PlatformSeeder::class);

    // Create test user
    User::factory()->create([
      'name' => 'Test User',
      'email' => 'test@example.com',
    ]);

    // Seed demo data (optional - useful for development)
    if (app()->environment('local', 'development')) {
      $this->call(DemoDataSeeder::class);
    }
  }
}
