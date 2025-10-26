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
        if (Schema::hasTable('news')) {
            return;
        }

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('news_sources')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('url')->nullable();
            $table->string('image_url')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('news')) {
            return;
        }

        Schema::dropIfExists('news');
    }
};
