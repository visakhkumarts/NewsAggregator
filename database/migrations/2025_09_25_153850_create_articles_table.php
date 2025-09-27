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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_source_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('external_id')->nullable(); // ID from the external API
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('url')->unique();
            $table->string('image_url')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at');
            $table->json('metadata')->nullable(); // Store additional API-specific data
            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            
            $table->index(['news_source_id', 'published_at']);
            $table->index(['category_id', 'published_at']);
            $table->index(['published_at']);
            $table->index(['is_featured', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
