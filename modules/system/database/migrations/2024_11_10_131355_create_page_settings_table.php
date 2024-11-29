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
        Schema::create('page_settings', function (Blueprint $table) {
            $table->id();
            $table->morphs('settingable');
            $table->string('header_title')->nullable();
            $table->string('header_link')->nullable();
            $table->string('header_image')->nullable();
            $table->boolean('product_index_status')->default(true);
            $table->enum('arrangement_of_products', [
                'Alphabetically', 'MostVisited', 'BestSeller' ,'MostComments', 'HighestScore', 'BasedOnCreationDate', 'BasedOnPrice'
            ])->default('BasedOnCreationDate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_settings');
    }
};
