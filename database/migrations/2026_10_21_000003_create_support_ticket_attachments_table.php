<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('support_ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('message_id')->nullable()->constrained('support_ticket_messages')->onDelete('cascade');
            $table->string('uploaded_by_type', 20);
            $table->foreignId('company_user_id')->nullable()->constrained('company_users')->onDelete('set null');
            $table->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->onDelete('set null');
            $table->string('original_filename', 255);
            $table->string('storage_key', 500);
            $table->string('mime_type', 150);
            $table->string('file_extension', 20)->nullable();
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('scan_status', 20)->default('PENDING'); // PENDING, CLEAN, INFECTED, FAILED
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();

            $table->index('ticket_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('support_ticket_attachments');
    }
};
