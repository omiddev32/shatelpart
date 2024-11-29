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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            /* Email */
            $table->string('email')->nullable()->index()->unique();
            $table->timestamp('email_verified_at')->nullable();

            /* Phone Number */
            $table->string('phone_number')->nullable()->index()->unique();
            $table->timestamp('phone_number_verified_at')->nullable();

            /* National Code */
            $table->string('code')->nullable()->index()->unique();
            $table->timestamp('code_verified_at')->nullable();
            $table->string('profile_picture')->nullable();
            $table->timestamp('birthdate')->nullable();
            $table->string('job')->nullable();
            $table->string('password')->nullable();
            $table->boolean('two_auth')->default(false);
            $table->string('two_auth_type')->default('phone_number');
            $table->timestamp('date_of_last_password_change')->nullable();
            $table->decimal("purchase_amount", 16, 2)->default(0);
            $table->decimal("wallet_balance", 16, 2)->after('purchase_amount')->default(0);
            $table->decimal("withdrawable_credit", 16, 2)->after('wallet_balance')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamp('register_datetime')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
