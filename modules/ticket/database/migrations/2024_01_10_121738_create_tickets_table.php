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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('ticket_number')->unique();
            $table->foreignId('ticket_category_id')->constrained('ticket_categories')->cascadeOnDelete();

            /* Start subject */
            $table->unsignedBigInteger('ticket_category_topic_id')->nullable();
            // Or
            $table->string('subject')->comment('Custom topic')->nullable();
             /* End subject */

            $table->boolean('critical')->default(false);
            $table->unsignedBigInteger('order_id')->nullable();

            $table->unsignedBigInteger('first_referred_to_admin');
            // $table->unsignedBigInteger('first_referred_to_organization');
            $table->unsignedBigInteger('last_referred_to_admin')->nullable();
            // $table->unsignedBigInteger('last_referred_to_organization')->nullable();
            $table->timestamp('last_referred_at')->nullable();

            $table->foreignId('ticket_status_id')->constrained('ticket_statuses')->cascadeOnDelete();
            $table->string('reason_for_closing')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
