<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('daily_reports', 'weekly_report_id')) {
            Schema::table('daily_reports', function (Blueprint $table) {
                $table->dropColumn('weekly_report_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('weekly_report_id')->nullable();
        });
    }
};