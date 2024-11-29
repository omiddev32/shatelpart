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
        Schema::create('order_settings', function (Blueprint $table) {
            $table->integer('id')->primary()->default(1);
            $table->string('vat_rate');
            $table->boolean('vat_status')->default(false);
            $table->integer('currency_api_duration')->comment('The duration of calling exchange rates')->default(5);
            $table->integer('order_validity_period')->comment('Order validity period')->default(15);
            $table->string('currency_api_driver');
            $table->timestamp('calling_time_currency_price_api')->nullable();
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_settings');
    }
};
