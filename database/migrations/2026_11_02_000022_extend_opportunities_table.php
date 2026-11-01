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
        Schema::table('opportunities', function (Blueprint $table) {
            $table->json('field_tags')->nullable()->after('description'); // Array of field_taxonomy IDs
            $table->string('location_type', 20)->default('onsite')->after('location'); // onsite, hybrid, remote
            $table->json('location_details')->nullable()->after('location_type'); // {county, town, gps_lat, gps_lng, address}
            $table->unsignedInteger('stipend_min')->nullable()->after('expiry_date');
            $table->unsignedInteger('stipend_max')->nullable()->after('stipend_min');
            $table->unsignedInteger('duration_months')->nullable()->after('stipend_max');
            $table->date('start_date')->nullable()->after('duration_months');
            $table->date('application_deadline')->nullable()->after('start_date');
            $table->unsignedInteger('slots_total')->default(1)->after('application_deadline');
            $table->unsignedInteger('slots_filled')->default(0)->after('slots_total');
            $table->text('requirements')->nullable()->after('slots_filled');
            $table->boolean('vetting_required')->default(true)->after('requirements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn([
                'field_tags',
                'location_type',
                'location_details',
                'stipend_min',
                'stipend_max',
                'duration_months',
                'start_date',
                'application_deadline',
                'slots_total',
                'slots_filled',
                'requirements',
                'vetting_required',
            ]);
        });
    }
};