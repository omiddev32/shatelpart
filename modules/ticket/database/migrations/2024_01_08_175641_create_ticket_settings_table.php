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
        Schema::create('ticket_settings', function (Blueprint $table) {
            $table->integer('id')->primary()->default(1);
            // Notification of tickets
            $table->boolean('send_by_email')->default(false);
            $table->boolean('send_by_sms')->default(false);
            $table->boolean('send_notification')->default(false);
            $table->integer('popup_opening_time')->default(24)->comment('Hours');

            // First stage notification
            $table->integer('first_stage_notification_time')->default(48)->comment('Hours');
            $table->boolean('first_stage_notification_to_super_admin')->default(false);
            $table->boolean('first_stage_notification_to_category_organization_manager')->default(false);
            $table->boolean('first_stage_notification_to_referred_person')->default(false);
            $table->jsonb('first_stage_notification_text')->nullable();

            // Second stage notification
            $table->integer('second_stage_notification_time')->default(96)->comment('Hours');
            $table->boolean('second_stage_notification_to_super_admin')->default(false);
            $table->boolean('second_stage_notification_to_category_organization_manager')->default(false);
            $table->boolean('second_stage_notification_to_referred_person')->default(false);
            $table->jsonb('second_stage_notification_text')->nullable();

            // General settings
            $table->integer('maximum_upload_size')->default(512)->comment('KB');
            $table->integer('ticket_closing_time')->default(240)->comment('Hours');
            $table->boolean('show_admin_name')->default(true)->comment('Display the name of admins in the response of users');

            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_settings');
    }
};
