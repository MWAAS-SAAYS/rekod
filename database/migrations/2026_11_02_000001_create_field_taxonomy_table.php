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
        Schema::create('field_taxonomy', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // e.g., 'EEE', 'EEE_BME', 'EEE_BME_MD'
            $table->string('name', 100); // e.g., 'Electrical & Electronic Engineering', 'Bio-Medical EEE', 'Medical Devices'
            $table->foreignId('parent_id')->nullable()->constrained('field_taxonomy')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(0); // 0=root, 1=major, 2=sub, 3=specialization
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['parent_id', 'level']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_taxonomy');
    }
};