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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->jsonb('currency_name');
            $table->string('iso')->index()->unique();
            $table->string('iso_code')->index()->unique();
            $table->string('last_price')->nullable();
            $table->jsonb('meta')->nullable();
            $table->string('driver_last_price_update')->nullable();
            $table->timestamp('last_price_update')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
