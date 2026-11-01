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
        Schema::create('epidemic_optins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('professional_type', 30); // nurse, doctor, engineer, teacher
            $table->json('skills')->nullable(); // ['ICU', 'Emergency', 'Pediatrics']
            $table->json('counties')->nullable(); // ['Nyeri', 'Nairobi', 'Mombasa']
            $table->timestamp('optin_at')->useCurrent();
            $table->timestamp('optout_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['professional_type', 'is_active']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epidemic_optins');
    }
};