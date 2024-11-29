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
        Schema::create('otps', function (Blueprint $table) {
            $table->increments('id')->index();
            $table->string('identifier')->comment('Email address or phone number');
            $table->string('token');
            $table->enum('identifier_type', [
                'email', 'phone_number'
            ])->default('phone_number');            
            $table->enum('type', [
                'register', 'login', 'change_password', 'reset_password'
            ])->default('register');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
