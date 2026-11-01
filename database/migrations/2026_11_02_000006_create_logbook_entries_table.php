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
        Schema::create('logbook_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->date('entry_date');
            $table->decimal('hours_worked', 4, 2);
            $table->json('field_values'); // Dynamic fields per logbook_schema: {field_name: value}
            $table->string('supervisor_name', 100)->nullable();
            $table->string('supervisor_signature_path', 500)->nullable(); // Supervisor co-sign
            $table->timestamp('supervisor_signed_at')->nullable();
            $table->enum('status', ['draft', 'submitted', 'supervisor_signed', 'hod_approved'])->default('draft');
            $table->foreignId('hod_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hod_approved_at')->nullable();
            $table->string('offline_id', 64)->nullable(); // Client-generated for sync
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->unique(['student_id', 'entry_date', 'offline_id']);
            $table->index(['student_id', 'entry_date']);
            $table->index(['company_id', 'status']);
            $table->index('offline_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_entries');
    }
};