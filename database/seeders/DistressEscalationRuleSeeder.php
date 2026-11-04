<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DistressEscalationRule;

/**
 * Seed distress escalation rules (university-wide).
 * Triggers: timeout_2h, severity_critical, keyword_abuse
 */
class DistressEscalationRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Rule 1: Timeout 2 hours - escalate to DEKU to CS and Security
        DistressEscalationRule::updateOrCreate(
            [
                'department_id' => null, // University-wide
                'trigger'       => 'timeout_2h',
            ],
            [
                'escalate_to_roles' => ['deku_to_cs', 'security'],
                'notify_channels'   => ['push', 'email', 'sms'],
                'is_active'         => true,
            ]
        );

        // Rule 2: Severity Critical - escalate to Dean and Security
        DistressEscalationRule::updateOrCreate(
            [
                'department_id' => null, // University-wide
                'trigger'       => 'severity_critical',
            ],
            [
                'escalate_to_roles' => ['dean', 'security'],
                'notify_channels'   => ['push', 'email', 'sms', 'whatsapp'],
                'is_active'         => true,
            ]
        );

        // Rule 3: Keyword Abuse - escalate to DEKU to CS
        DistressEscalationRule::updateOrCreate(
            [
                'department_id' => null, // University-wide
                'trigger'       => 'keyword_abuse',
            ],
            [
                'escalate_to_roles' => ['deku_to_cs'],
                'notify_channels'   => ['push', 'email'],
                'is_active'         => true,
            ]
        );

        // Rule 4: Timeout 30 minutes - escalate to DEKU to CS (early warning)
        DistressEscalationRule::updateOrCreate(
            [
                'department_id' => null, // University-wide
                'trigger'       => 'timeout_30m',
            ],
            [
                'escalate_to_roles' => ['deku_to_cs'],
                'notify_channels'   => ['push', 'email'],
                'is_active'         => true,
            ]
        );

        // Rule 5: No Check-in 24h - escalate to DEKU to CS and Security
        DistressEscalationRule::updateOrCreate(
            [
                'department_id' => null, // University-wide
                'trigger'       => 'no_checkin_24h',
            ],
            [
                'escalate_to_roles' => ['deku_to_cs', 'security'],
                'notify_channels'   => ['push', 'email', 'sms'],
                'is_active'         => true,
            ]
        );

        $this->command?->info('Distress escalation rules seeded successfully.');
    }
}
