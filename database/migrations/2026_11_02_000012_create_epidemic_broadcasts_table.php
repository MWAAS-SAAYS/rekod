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
        Schema::create('epidemic_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('message');
            $table->json('target_skills')->nullable(); // ['ICU', 'Emergency']
            $table->json('target_counties')->nullable(); // ['Nyeri', 'Nairobi']
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->foreignId('sent_by')->constrained('users');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
            
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epidemic_broadcasts');
    }
};