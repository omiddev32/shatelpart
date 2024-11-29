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
        Schema::create('face_value_apis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            /* Original product id */
            $table->string('product_id')->index();
            $table->string('face_value_id')->unique()->index();
            $table->enum('type', ['fixed', 'range'])->default('fixed');
            $table->string('face_value_currency');
            $table->float('face_value')->default(0);

            /* if type === 'range'*/
            $table->float('max_face_value')->default(0);
            $table->float('face_value_step')->default(0);

            /* if type === 'fixed'*/
            $table->text('definition')->nullable();


            $table->string('cost_currency');
            $table->float('cost')->default(0);

            /* if type === 'range'*/
            $table->float('max_cost')->default(0);
            $table->boolean('promotion')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_value_apis');
    }
};
