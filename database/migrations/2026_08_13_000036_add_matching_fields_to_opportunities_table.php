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
            if (!Schema::hasColumn('opportunities', 'program_code')) {
                $table->string('program_code')->nullable(); // e.g., E028, C001
            }
            if (!Schema::hasColumn('opportunities', 'is_paid')) {
                $table->boolean('is_paid')->default(false);
            }
            if (!Schema::hasColumn('opportunities', 'slots_count')) {
                $table->integer('slots_count')->default(1);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('opportunities', 'program_code')) {
                $columnsToDrop[] = 'program_code';
            }
            if (Schema::hasColumn('opportunities', 'is_paid')) {
                $columnsToDrop[] = 'is_paid';
            }
            if (Schema::hasColumn('opportunities', 'slots_count')) {
                $columnsToDrop[] = 'slots_count';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};