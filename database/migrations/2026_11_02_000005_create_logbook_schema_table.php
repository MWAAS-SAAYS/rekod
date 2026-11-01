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
        Schema::create('logbook_schema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('administrative_units')->cascadeOnDelete();
            $table->string('field_name', 50); // e.g., 'daily_tasks', 'hours_worked', 'supervisor_signature'
            $table->string('field_label', 100); // Human readable label
            $table->string('field_type', 20); // text, textarea, number, date, select, signature, file
            $table->json('field_options')->nullable(); // For select fields: ['option1', 'option2']
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['department_id', 'field_name']);
            $table->index(['department_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_schema');
    }
};