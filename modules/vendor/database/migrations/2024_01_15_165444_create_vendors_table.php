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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('service_name')->default('cysend');
            $table->integer('priority')->default(1);
            $table->string('currency')->default('Dollar');
            $table->decimal("balance", 16, 2)->default(0);
            $table->integer("number_of_products_provided")->default(0);
            $table->integer("number_of_products_is_not_provided")->default(0);
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->text('token')->nullable();
            $table->longText('extra_data')->nullable();
            $table->timestamp("latest_product_updates")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
