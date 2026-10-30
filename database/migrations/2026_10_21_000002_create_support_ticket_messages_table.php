<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->string('sender_type', 20); // COMPANY_USER or PLATFORM_ADMIN
            $table->foreignId('company_user_id')->nullable()->constrained('company_users')->onDelete('set null');
            $table->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->onDelete('set null');
            $table->string('message_type', 20)->default('REPLY'); // REPLY, SYSTEM
            $table->text('message_body');
            $table->boolean('is_internal_note')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ticket_id', 'created_at']);
            $table->index('is_internal_note');
        });
    }

    public function down(): void {
        Schema::dropIfExists('support_ticket_messages');
    }
};
