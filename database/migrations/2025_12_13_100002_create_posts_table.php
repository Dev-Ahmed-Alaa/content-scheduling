<?php

use App\Enums\PostStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('posts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('title');
      $table->text('content');
      $table->string('image_url')->nullable();
      $table->timestamp('scheduled_time')->nullable();
      $table->string('status')->default(PostStatus::DRAFT->value);
      $table->timestamp('published_at')->nullable();
      $table->timestamps();
      $table->softDeletes();

      // Indexes for common query patterns
      $table->index(['user_id', 'status'], 'idx_user_status');
      $table->index(['scheduled_time', 'status'], 'idx_scheduled_time');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('posts');
  }
};
