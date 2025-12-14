<?php

use App\Enums\PlatformStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('post_platform', function (Blueprint $table) {
      $table->id();
      $table->foreignId('post_id')->constrained()->cascadeOnDelete();
      $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
      $table->string('platform_status')->default(PlatformStatus::PENDING->value);
      $table->timestamp('published_at')->nullable();
      $table->text('error_message')->nullable();
      $table->timestamps();

      $table->unique(['post_id', 'platform_id'], 'unique_post_platform');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('post_platform');
  }
};
