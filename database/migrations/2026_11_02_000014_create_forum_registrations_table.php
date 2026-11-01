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
        Schema::create('forum_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained('engagement_forums')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['company_hr', 'student', 'admin', 'speaker'])->default('student');
            $table->boolean('attended')->default(false);
            $table->unsignedTinyInteger('feedback_score')->nullable(); // 1-5
            $table->text('feedback_text')->nullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['forum_id', 'user_id']);
            $table->index(['forum_id', 'attended']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_registrations');
    }
};