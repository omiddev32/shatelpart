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
        Schema::create('product_apis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->enum('zone', ['Global', 'Others', 'Eurozone', 'Local', 'GCC', 'NORTH_AMERICA'])->default('Global');
            $table->string('product_id')->unique()->index();
            $table->unsignedBigInteger('connected_to')->unsigned()->index()->nullable();
            $table->string('name')->index();
            $table->longText('description')->nullable();
            $table->string('logo_url')->nullable();
            /*
                Instant: means that the top up will credited directly to the customer account.
                Prepaid code: means that the top up will be delivered as a prepaid code with usage instructions. The customer will have to follow the instructions to use his prepaid code. 
                SMS: means that the product is a text message. 
                HLR: means that the product is a mobile operator detection.
            */
            $table->enum('type', [
                'instant', 'prepaid_code', 'sms', 'hlr'
            ])->default('instant');
            $table->boolean('promotion')->default(false);
            $table->boolean('maintenance')->default(false);
            $table->text('countries')->nullable();
            $table->text('face_values')->nullable();
            $table->longText('beneficiary_information')->nullable();
            $table->longText('usage_instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_apis');
    }
};
