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
        Schema::create('letter_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->constrained('letter_templates');
            $table->json('template_data'); // Filled variables
            $table->string('generated_pdf_path', 500)->nullable(); // MinIO path
            $table->enum('status', ['draft', 'hod_review', 'cod_review', 'signed', 'dispatched'])->default('draft');
            $table->foreignId('hod_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hod_reviewed_at')->nullable();
            $table->text('hod_comments')->nullable();
            $table->foreignId('cod_signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cod_signed_at')->nullable();
            $table->string('cod_signature_path', 500)->nullable(); // Digital signature image
            $table->timestamp('dispatched_at')->nullable();
            $table->enum('dispatched_via', ['email', 'print', 'both'])->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
            $table->index(['hod_reviewed_by', 'status']);
            $table->index(['cod_signed_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_requests');
    }
};