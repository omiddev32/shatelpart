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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->string('variant_id');
            $table->string('variant_value');

            $table->string('reference_number')->unique()->index();
            /* api tracking code */
            $table->string('tracking_code')->unique()->nullable();
            $table->string('order_number')->nullable()->unique()->index();
            $table->integer('count')->default(1);
            $table->jsonb('meta_data')->nullable();
            $table->jsonb('beneficiary_information')->nullable();
            $table->string('status')->default('initial');
            $table->float('product_price')->nullable();
            $table->float('tax_price')->nullable();
            $table->float('price_paid')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->boolean('send_to_user')->default(false);
            $table->unsignedBigInteger('delivery_type_id')->unsigned()->index()->nullable();
            $table->foreign('delivery_type_id')->references('id')->on('brands');
            $table->string('delivery_address')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
