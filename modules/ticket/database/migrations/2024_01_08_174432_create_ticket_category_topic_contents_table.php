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
        Schema::create('ticket_category_topic_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_category_topic_id')->constrained('ticket_category_topics')->cascadeOnDelete();
            $table->string('title');
            $table->string('language')->default(app()->getLocale());
            $table->text('keywords')->nullable();
            $table->text('link')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_category_topic_contents');
    }
};
