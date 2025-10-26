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
        if (Schema::hasTable('news_sources')) {
            return;
        }

        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('news_sources')) {
            return;
        }

        Schema::dropIfExists('news_sources');
    }
};
