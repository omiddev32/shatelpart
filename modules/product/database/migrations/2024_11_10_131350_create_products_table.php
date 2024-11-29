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
        Schema::create('products', function (Blueprint $table) {
            /* General */
            $table->id();
            $table->string('name')->index();
            $table->jsonb('display_name');

            /* Relational features */

            $table->unsignedBigInteger('zone_id')->unsigned()->index();
            $table->foreign('zone_id')->references('id')->on('zones');
            /* ----- */
            $table->unsignedBigInteger('brand_id')->unsigned()->index()->nullable();
            $table->foreign('brand_id')->references('id')->on('brands');
            /* ----- */
            $table->unsignedBigInteger('sub_brand_id')->unsigned()->index()->nullable();
            $table->foreign('sub_brand_id')->references('id')->on('brands');
            /* ----- */
            $table->unsignedBigInteger('product_type_id')->unsigned()->index();
            $table->foreign('product_type_id')->references('id')->on('product_types');
            /* ----- */ 

            $table->enum('vendor_type', ['API', 'Store'])->default('API');
            $table->jsonb('beneficiary_information')->nullable();
            $table->string('main_image')->nullable();
            $table->text('images_data')->nullable();
            $table->text('hashtags')->nullable();

            /* Cash Fields */
            $table->enum('price_type', ['fixed', 'range', 'single'])->default('fixed');

            /* Price Data */
            $table->string('currency_price')->nullable();
            $table->float('min_price')->default(0);
            $table->float('max_price')->default(0);
            /* ===========================  */
            $table->string('cost_currency')->nullable()->after('max_price');
            $table->float('min_cost')->default(0)->after('cost_currency');
            $table->float('max_cost')->default(0)->after('min_cost');

            /* 
                Json => introduction -> 'Short Introduction', 
                Boolean => introduction_status, 
                Json => application -> 'Application of the product', 
                Boolean => application_status, 
                Json => usage_method -> 'Usage Method', 
                Boolean => usage_method_status, 
                Boolean => faq_status, 
                Json => videos, 
                Boolean => videos_status, 
                Boolean => categories_tagable, 
             */
            $table->jsonb('meta_data')->nullable();

            /*
                Boolean => shipping_after_first_purchase -> 'Send to others after first purchase'
                Boolean => commentable
                Boolean => rateable
                Boolean => auto_create_ticket
                Boolean => custom_notification
                Text => notification_content
            */
            $table->jsonb('settings_data')->nullable();
            $table->jsonb('finance_data')->nullable();

            /* Counter */
            $table->unsignedBigInteger('number_of_visits')->default(0);
            $table->unsignedBigInteger('number_of_comments')->default(0);
            $table->unsignedBigInteger('number_of_sales')->default(0);
            $table->unsignedBigInteger('number_of_points')->default(0); 

            /* Statuses */
            $table->enum('status', ['Active', 'Inactive', 'ActiveNoInventory'])->default('Active');
            $table->boolean('promotion')->default(false);
            $table->boolean('maintenance')->default(false);
            $table->timestamp('disabled_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
