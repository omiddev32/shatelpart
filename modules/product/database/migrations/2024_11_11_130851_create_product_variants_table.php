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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('display_name')->nullable();
            $table->enum('type', ['range', 'fixed', 'pincode'])->default('fixed');
            $table->boolean('selected')->default(false);

            /* if type === 'range'*/
            $table->float('min_face_value')->default(0);
            $table->float('max_face_value')->default(0);
            $table->float('face_value_step')->default(0);
            $table->jsonb('range_suggestion')->nullable();
            $table->boolean('only_suggestion')->default(false);

            /* if type === 'pincode' */
            $table->float('purchase_price')->default(0);
            $table->float('selling_price')->default(0);
            $table->unsignedBigInteger('number_of_pincode')->default(0);

            $table->enum('status', ['Active', 'Inactive', 'ActiveNoInventory'])->default('Active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
