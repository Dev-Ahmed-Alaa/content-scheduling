<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $platforms = config('platforms.defaults', []);

    foreach ($platforms as $platform) {
      Platform::updateOrCreate(
        ['type' => $platform['type']],
        [
          'name' => $platform['name'],
          'character_limit' => $platform['character_limit'],
          'is_active' => $platform['is_active'],
        ]
      );
    }

    $this->command->info('Platforms seeded successfully.');
  }
}
