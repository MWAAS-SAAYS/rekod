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
        Schema::create('engagement_forums', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('event_type', ['virtual', 'physical', 'hybrid'])->default('virtual');
            $table->timestamp('event_date');
            $table->unsignedInteger('duration_minutes')->default(120);
            $table->string('location', 255)->nullable(); // Physical venue or meeting link
            $table->foreignId('organizer_id')->constrained('users');
            $table->json('target_fields')->nullable(); // field_taxonomy IDs
            $table->unsignedInteger('max_attendees')->nullable();
            $table->enum('status', ['scheduled', 'live', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
            
            $table->index(['event_date', 'status']);
            $table->index('organizer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagement_forums');
    }
};