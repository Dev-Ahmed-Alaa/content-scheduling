<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates test users with different scenarios for testing:
     * - Test User: Basic user for API testing (password: password)
     * - Demo User: User with all platforms activated (password: password)
     * - Power User: User for rate limit testing (password: password)
     */
    public function run(): void
    {
        $platforms = Platform::all();

        // 1. Test User - Basic user for API testing
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Activate X and Instagram for test user
        $testUser->platforms()->attach([
            $platforms->where('type', 'x')->first()->id => ['is_active' => true],
            $platforms->where('type', 'instagram')->first()->id => ['is_active' => true],
        ]);

        $this->command->info('Created Test User: test@example.com (password: password)');

        // 2. Demo User - User with all platforms activated
        $demoUser = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);

        // Activate all platforms for demo user
        foreach ($platforms as $platform) {
            $demoUser->platforms()->attach($platform->id, ['is_active' => true]);
        }

        $this->command->info('Created Demo User: demo@example.com (password: password)');

        // 3. Power User - User for rate limit testing
        $powerUser = User::factory()->create([
            'name' => 'Power User',
            'email' => 'power@example.com',
            'password' => Hash::make('password'),
        ]);

        // Activate LinkedIn and Facebook for power user
        $powerUser->platforms()->attach([
            $platforms->where('type', 'linkedin')->first()->id => ['is_active' => true],
            $platforms->where('type', 'facebook')->first()->id => ['is_active' => true],
        ]);

        $this->command->info('Created Power User: power@example.com (password: password)');

        // 4. Admin User - For administrative testing
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // Activate all platforms for admin
        foreach ($platforms as $platform) {
            $adminUser->platforms()->attach($platform->id, ['is_active' => true]);
        }

        $this->command->info('Created Admin User: admin@example.com (password: password)');

        $this->command->newLine();
        $this->command->info('All users created with password: "password"');
    }
}
