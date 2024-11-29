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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->integer('original_id')->nullable();
            $table->jsonb('name');
            $table->jsonb('description')->nullable();
            $table->string('symbol')->unique();
            $table->string('symbol_2')->unique();
            $table->string('image')->nullable();
            $table->string('big_image')->nullable();
            $table->string('emoji_code')->nullable();
            $table->string('phone_number_prefix')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
