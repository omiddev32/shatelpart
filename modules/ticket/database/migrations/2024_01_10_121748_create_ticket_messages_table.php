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
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->morphs('modelable');
            $table->text('text')->nullable();
            $table->enum('type' ,[
                'message', 'note', 'referred'
            ])->default('message');

            // $table->unsignedBigInteger('referred_from_organization')->nullable();
            $table->unsignedBigInteger('referred_from_admin')->nullable();
            // $table->unsignedBigInteger('referred_to_organization')->nullable();
            $table->unsignedBigInteger('referred_to_admin')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
