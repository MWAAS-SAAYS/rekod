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
        Schema::create('distress_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hod_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['unread', 'acknowledged', 'in_progress', 'resolved', 'escalated'])->default('unread');
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('escalated_at')->nullable();
            $table->json('escalated_to')->nullable(); // Array of user IDs: DeKUTOCS, Security, Dean
            $table->string('escalation_reason', 100)->nullable(); // timeout, severity, manual
            $table->timestamps();
            
            $table->index(['hod_id', 'status']);
            $table->index(['student_id', 'created_at']);
            $table->index('escalated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distress_messages');
    }
};