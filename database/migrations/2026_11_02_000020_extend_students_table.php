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
        Schema::table('students', function (Blueprint $table) {
            $table->enum('attachment_status', ['not_started', 'seeking', 'placed', 'active', 'completed'])->default('not_started')->after('reg_no');
            $table->foreignId('current_company_id')->nullable()->constrained('companies')->nullOnDelete()->after('attachment_status');
            $table->date('attachment_start_date')->nullable()->after('current_company_id');
            $table->date('attachment_end_date')->nullable()->after('attachment_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'attachment_status',
                'current_company_id',
                'attachment_start_date',
                'attachment_end_date',
            ]);
        });
    }
};