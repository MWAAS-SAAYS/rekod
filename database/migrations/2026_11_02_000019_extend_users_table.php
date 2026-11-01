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
        Schema::table('users', function (Blueprint $table) {
            $table->char('national_id_hash', 64)->nullable()->unique()->after('phone_number');
            $table->json('kdpa_consent')->nullable()->after('national_id_hash'); // Granular consent flags
            $table->string('avatar_url', 500)->nullable()->after('kdpa_consent');
            $table->string('insurance_provider', 100)->nullable()->after('avatar_url');
            $table->string('insurance_policy_no', 50)->nullable()->after('insurance_provider');
            $table->date('insurance_expiry')->nullable()->after('insurance_policy_no');
            $table->boolean('insurance_verified')->default(false)->after('insurance_expiry');
            $table->string('offline_logbook_sync_token', 64)->nullable()->after('insurance_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'national_id_hash',
                'kdpa_consent',
                'avatar_url',
                'insurance_provider',
                'insurance_policy_no',
                'insurance_expiry',
                'insurance_verified',
                'offline_logbook_sync_token',
            ]);
        });
    }
};