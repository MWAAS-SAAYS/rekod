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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('registration_no', 50)->nullable()->unique()->after('name');
            $table->string('kra_pin', 20)->nullable()->after('registration_no');
            $table->boolean('kra_verified')->default(false)->after('kra_pin');
            $table->timestamp('kra_verified_at')->nullable()->after('kra_verified');
            $table->string('sector', 100)->nullable()->after('kra_verified_at');
            $table->json('sub_sectors')->nullable()->after('sector'); // Array of field_taxonomy codes
            $table->json('physical_address')->nullable()->after('address'); // {county, town, gps_lat, gps_lng}
            $table->string('contact_person', 100)->nullable()->after('email');
            $table->string('contact_phone', 20)->nullable()->after('contact_person');
            $table->string('website', 255)->nullable()->after('contact_phone');
            $table->text('description')->nullable()->after('website');
            $table->string('logo_url', 500)->nullable()->after('description');
            $table->enum('vetting_status', ['pending', 'approved', 'rejected', 'flagged'])->default('pending')->after('logo_url');
            $table->text('vetting_notes')->nullable()->after('vetting_status');
            $table->foreignId('vetted_by')->nullable()->constrained('users')->nullOnDelete()->after('vetting_notes');
            $table->timestamp('vetted_at')->nullable()->after('vetted_by');
            $table->enum('plan_tier', ['free', 'basic', 'professional', 'enterprise'])->default('free')->after('vetted_at');
            $table->timestamp('plan_expires_at')->nullable()->after('plan_tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'registration_no',
                'kra_pin',
                'kra_verified',
                'kra_verified_at',
                'sector',
                'sub_sectors',
                'physical_address',
                'contact_person',
                'contact_phone',
                'website',
                'description',
                'logo_url',
                'vetting_status',
                'vetting_notes',
                'vetted_by',
                'vetted_at',
                'plan_tier',
                'plan_expires_at',
            ]);
        });
    }
};