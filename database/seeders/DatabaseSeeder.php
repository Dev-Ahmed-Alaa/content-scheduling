<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Seeding Order:
     * 1. PlatformSeeder - Creates the 4 social media platforms (X, Instagram, LinkedIn, Facebook)
     * 2. UserSeeder - Creates test users with platform activations
     * 3. DemoDataSeeder - Creates posts with various statuses for testing (local/dev only)
     *
     * Test Credentials (all passwords are "password"):
     * - test@example.com   : Basic test user with X and Instagram activated
     * - demo@example.com   : Demo user with all platforms activated
     * - power@example.com  : Power user for rate limit testing (8 posts today)
     * - admin@example.com  : Admin user with all platforms activated
     *
     * Run: php artisan db:seed
     * Fresh: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('🚀 Starting Content Scheduling Database Seeder');
        $this->command->newLine();

        // 1. Seed platforms (required for everything else)
        $this->command->info('📱 Seeding platforms...');
        $this->call(PlatformSeeder::class);
        $this->command->newLine();

        // 2. Seed users with platform activations
        $this->command->info('👥 Seeding users...');
        $this->call(UserSeeder::class);
        $this->command->newLine();

        // 3. Seed demo data (only in local/development environments)
        if (app()->environment('local', 'development', 'testing')) {
            $this->command->info('📝 Seeding demo data...');
            $this->call(DemoDataSeeder::class);
            $this->command->newLine();
        }

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed!');
        $this->command->newLine();
        $this->printTestCredentials();
    }

    /**
     * Print test credentials for easy reference.
     */
    private function printTestCredentials(): void
    {
        $this->command->table(
            ['Email', 'Password', 'Description'],
            [
                ['test@example.com', 'password', 'Basic test user (X, Instagram)'],
                ['demo@example.com', 'password', 'Demo user (all platforms)'],
                ['power@example.com', 'password', 'Rate limit testing (8/10 today)'],
                ['admin@example.com', 'password', 'Admin user (all platforms)'],
            ]
        );
    }
}
