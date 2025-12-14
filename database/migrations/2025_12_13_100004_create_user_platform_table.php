<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('user_platform', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->unique(['user_id', 'platform_id'], 'unique_user_platform');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('user_platform');
  }
};
