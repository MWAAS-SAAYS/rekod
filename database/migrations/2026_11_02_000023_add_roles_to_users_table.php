<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new roles to the existing users table role enum/check
        // The users table uses a string 'role' column, so we just document the new roles
        // Roles: student, industry, university, admin, hod, cod, directorate, deku_to_cs, security, alumni
        
        // This migration ensures the role column can accommodate new values
        // No schema change needed for string column, but we add a comment for documentation
        DB::statement("ALTER TABLE users COMMENT = 'Roles: student, industry, university, admin, hod, cod, directorate, deku_to_cs, security, alumni'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users COMMENT = ''");
    }
};