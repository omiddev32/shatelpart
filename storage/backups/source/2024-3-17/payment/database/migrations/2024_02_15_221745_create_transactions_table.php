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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedDecimal('amount', 16, 4);
            $table->enum('mode', [
                'Increment', 'Decrement'
            ])->default('increment');
            $table->unsignedBigInteger('admin_id')->nullable();

            $table->enum('type', ['GiftCredit', 'BankDeposit', 'FixDiscrepancy', 'GiftCreditDeduction'])
                ->default('BankDeposit');
            $table->boolean('reternable')->default(false);
            $table->string('gateway')->nullable()->index();

            $table->string('reference_number')->nullable()->index();
            $table->string('tracking_code')->nullable()->index();

            $table->enum('status', [
                'init', 'success', 'failed', 'cancel'
            ])->default('init');

            /* Card data */
            $table->unsignedBigInteger('user_bank_account_id')->nullable();
            $table->string('card_number')->nullable();
            $table->string('full_name')->nullable();

            /* User|Guest data */
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('description')->nullable();

            $table->string('ip', 20)->nullable();

            $table->text('errors')->nullable();
            $table->text('extra')->nullable();
            $table->json('gateway_data')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
