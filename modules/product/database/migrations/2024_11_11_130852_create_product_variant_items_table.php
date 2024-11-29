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
        Schema::create('product_variant_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('variant_id')->unsigned()->index()->nullable();
            $table->foreign('variant_id')->references('id')->on('product_variants');

            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('face_value_id')->nullable();
            
            $table->enum('type', ['fixed', 'range', 'single'])->default('fixed');
            $table->string('face_value_currency');
            $table->float('face_value')->default(0);

            /* if type === 'range'*/
            $table->float('max_face_value')->default(0);
            $table->float('face_value_step')->default(0);

            /* if type === 'fixed'*/
            $table->string('definition')->nullable();
            $table->string('cost_currency');
            $table->float('cost')->default(0);

            /* if type === 'range'*/
            $table->float('max_cost')->default(0);
            $table->boolean('promotion')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_items');
    }
};
