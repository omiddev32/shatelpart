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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete('cascade');
            $table->string('address');
            $table->string('postal_code');
            $table->foreignId('province_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->boolean('my_address')->comment('I am the recipient of my order');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_family')->nullable();
            $table->string('recipient_phone_number')->nullable();
            $table->string('address_image')->nullable();
            $table->text('map_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
