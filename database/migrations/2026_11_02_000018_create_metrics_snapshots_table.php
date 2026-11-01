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
        Schema::create('metrics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date');
            $table->string('metric_type', 50); // placement_rate, sector_dist, retention, transition
            $table->string('dimension', 50)->nullable(); // department, company, county, field
            $table->string('dimension_value', 100)->nullable();
            $table->decimal('value', 15, 4);
            $table->unsignedInteger('sample_size')->nullable();
            $table->string('anonymization_method', 50)->nullable(); // k_anonymity_k5_dp_eps05
            $table->timestamps();
            
            $table->unique(['snapshot_date', 'metric_type', 'dimension', 'dimension_value']);
            $table->index(['snapshot_date', 'metric_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrics_snapshots');
    }
};