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
        Schema::create('tracer_studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('users')->cascadeOnDelete();
            $table->enum('survey_round', ['6_month', '12_month', '24_month']);
            $table->enum('employment_status', ['employed', 'self_employed', 'studying', 'unemployed', 'not_seeking'])->nullable();
            $table->string('current_role', 100)->nullable();
            $table->string('current_company', 255)->nullable();
            $table->string('sector', 100)->nullable();
            $table->string('salary_band', 20)->nullable(); // <20k, 20-50k, 50-100k, 100-200k, 200k+
            $table->unsignedTinyInteger('satisfaction_score')->nullable(); // 1-5
            $table->unsignedTinyInteger('relevance_score')->nullable(); // 1-5
            $table->text('feedback')->nullable();
            $table->boolean('consent_research')->default(false);
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['alumni_id', 'survey_round']);
            $table->index(['survey_round', 'employment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracer_studies');
    }
};