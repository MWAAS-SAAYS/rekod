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
        Schema::create('student_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('problem_statement');
            $table->text('brief_solution');
            $table->string('full_solution_path', 500)->nullable();
            $table->json('field_tags');
            $table->boolean('interdisciplinary')->default(false);
            $table->enum('ip_ownership', ['student', 'university', 'joint'])->default('student');
            $table->text('ip_notes')->nullable();
            $table->enum('hod_approval_status', ['pending', 'approved', 'rejected', 'revisions'])->default('pending');
            $table->foreignId('hod_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hod_approved_at')->nullable();
            $table->text('hod_comments')->nullable();
            $table->enum('visibility', ['private', 'hod_only', 'vetted_companies', 'public'])->default('private');
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();
            
            $table->index(['student_id', 'hod_approval_status']);
            $table->index('visibility');
            $table->index('interdisciplinary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_projects');
    }
};