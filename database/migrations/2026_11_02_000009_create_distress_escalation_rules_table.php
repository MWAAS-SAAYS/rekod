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
        Schema::create('distress_escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained('administrative_units')->nullOnDelete(); // NULL = university-wide
            $table->string('trigger', 30); // timeout_2h, severity_critical, keyword_abuse
            $table->json('escalate_to_roles'); // ['deku_to_cs', 'security', 'dean']
            $table->json('notify_channels'); // ['push', 'email', 'sms', 'whatsapp']
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['department_id', 'trigger']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distress_escalation_rules');
    }
};