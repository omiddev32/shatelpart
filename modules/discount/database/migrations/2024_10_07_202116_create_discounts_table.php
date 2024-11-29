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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('prefix')->nullable();
            $table->enum('amount_type', ['percentage', 'fixed']);
            $table->string('amount_type_value');
            $table->float('max_value')->nullable();
            $table->enum('target_type', ['product', 'category', 'brand'])->default('product');

            /* For Reports */
            $table->bigInteger('number_of_uses')->default(0);
            $table->float('amount_used')->default(0);
            
            $table->boolean('restrictions_use')->default(false);
            $table->bigInteger('restrictions_use_value')->nullable();
            $table->enum('user_restrictions', ['all', 'new', 'new_with_range'])->default('product');
            $table->timestamp('user_restrictions_from')->nullable();
            $table->timestamp('user_restrictions_to')->nullable();
            $table->float('deactivation_amount')->nullable();
            $table->timestamp('expiration_date')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
