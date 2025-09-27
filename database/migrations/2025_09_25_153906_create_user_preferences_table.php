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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('preferred_sources')->nullable(); // Array of news source IDs
            $table->json('preferred_categories')->nullable(); // Array of category IDs
            $table->json('preferred_authors')->nullable(); // Array of author names
            $table->string('language')->default('en');
            $table->string('country')->default('us');
            $table->integer('articles_per_page')->default(20);
            $table->boolean('show_images')->default(true);
            $table->boolean('auto_refresh')->default(false);
            $table->integer('refresh_interval')->default(300); // seconds
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
