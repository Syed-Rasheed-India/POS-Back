<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('ticket_number', 30)->unique();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('company_user_id')->nullable()->constrained('company_users')->onDelete('set null');
            $table->foreignId('assigned_admin_id')->nullable()->constrained('platform_admins')->onDelete('set null');
            $table->string('subject', 200);
            $table->text('description');
            $table->string('category', 50)->default('GENERAL');
            $table->string('priority', 20)->default('NORMAL'); // LOW, NORMAL, HIGH, CRITICAL
            $table->string('status', 30)->default('OPEN');     // OPEN, ASSIGNED, IN_PROGRESS, WAITING_FOR_CUSTOMER, RESOLVED, CLOSED
            $table->string('source', 30)->default('WEB');
            $table->string('related_module', 50)->nullable();
            $table->string('related_reference', 100)->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('resolution_summary')->nullable();
            $table->unsignedSmallInteger('satisfaction_rating')->nullable();
            $table->text('satisfaction_feedback')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status', 'created_at']);
            $table->index(['assigned_admin_id', 'status', 'priority']);
            $table->index(['status', 'priority', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('support_tickets');
    }
};
