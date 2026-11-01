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
        Schema::create('student_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->unsignedInteger('accuracy_meters')->nullable();
            $table->string('location_name', 255)->nullable(); // Company name or "Remote"
            $table->boolean('share_with_hod')->default(true); // Opt-in privacy control
            $table->timestamp('recorded_at')->useCurrent();
            $table->enum('source', ['mobile', 'manual', 'company_checkin'])->default('mobile');
            $table->timestamps();
            
            $table->index(['student_id', 'recorded_at']);
            $table->index('share_with_hod');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_locations');
    }
};