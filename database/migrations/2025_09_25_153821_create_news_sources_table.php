<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('api_provider'); // newsapi, guardian, nytimes
            $table->string('api_endpoint');
            $table->json('api_config')->nullable(); // Store API-specific configuration
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // For ordering sources
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('website_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
