<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FieldTaxonomy;

/**
 * Seed field taxonomy hierarchy:
 * Level 0 (root): EEE, IT, Civil, Mechanical
 * Level 1 (major): EEE_BME (child of EEE), etc.
 * Level 2 (sub): EEE_BME_MD (child of EEE_BME), etc.
 */
class FieldTaxonomySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Level 0 - Root fields
        $eee = $this->createRootField(
            'EEE',
            'Electrical & Electronic Engineering',
            'Core electrical and electronic engineering disciplines',
            1
        );

        $it = $this->createRootField(
            'IT',
            'Information Technology',
            'Information technology and computer science disciplines',
            2
        );

        $civil = $this->createRootField(
            'CIVIL',
            'Civil Engineering',
            'Civil and structural engineering disciplines',
            3
        );

        $mechanical = $this->createRootField(
            'MECH',
            'Mechanical Engineering',
            'Mechanical and manufacturing engineering disciplines',
            4
        );

        // Level 1 - Major specializations under EEE
        $eeeBme = $this->createChildField(
            'EEE_BME',
            'Bio-Medical Electrical Engineering',
            $eee->id,
            'Bio-medical applications of electrical engineering',
            1
        );

        $this->createChildField(
            'EEE_PWR',
            'Power Systems Engineering',
            $eee->id,
            'Electrical power generation, transmission and distribution',
            2
        );

        $this->createChildField(
            'EEE_CTRL',
            'Control & Instrumentation',
            $eee->id,
            'Control systems and instrumentation engineering',
            3
        );

        $this->createChildField(
            'EEE_TEL',
            'Telecommunications Engineering',
            $eee->id,
            'Telecommunications and signal processing',
            4
        );

        // Level 1 - Major specializations under IT
        $itSoftware = $this->createChildField(
            'IT_SW',
            'Software Engineering',
            $it->id,
            'Software development and engineering practices',
            1
        );

        $this->createChildField(
            'IT_NET',
            'Network Engineering',
            $it->id,
            'Computer networks and infrastructure',
            2
        );

        $this->createChildField(
            'IT_DATA',
            'Data Science & Analytics',
            $it->id,
            'Data science, machine learning and analytics',
            3
        );

        $this->createChildField(
            'IT_CYBER',
            'Cybersecurity',
            $it->id,
            'Information security and cyber defense',
            4
        );

        // Level 1 - Major specializations under Civil
        $this->createChildField(
            'CIVIL_STRUCT',
            'Structural Engineering',
            $civil->id,
            'Structural analysis and design',
            1
        );

        $this->createChildField(
            'CIVIL_WATER',
            'Water Resources Engineering',
            $civil->id,
            'Water supply, sanitation and hydraulic engineering',
            2
        );

        $this->createChildField(
            'CIVIL_TRANSP',
            'Transportation Engineering',
            $civil->id,
            'Transportation systems and infrastructure',
            3
        );

        $this->createChildField(
            'CIVIL_GEO',
            'Geotechnical Engineering',
            $civil->id,
            'Soil mechanics and foundation engineering',
            4
        );

        // Level 1 - Major specializations under Mechanical
        $this->createChildField(
            'MECH_THERMO',
            'Thermodynamics & Energy Systems',
            $mechanical->id,
            'Thermodynamics, heat transfer and energy systems',
            1
        );

        $this->createChildField(
            'MECH_DESIGN',
            'Machine Design & Manufacturing',
            $mechanical->id,
            'Mechanical design, CAD/CAM and manufacturing',
            2
        );

        $this->createChildField(
            'MECH_AUTO',
            'Automotive Engineering',
            $mechanical->id,
            'Automotive systems and vehicle engineering',
            3
        );

        $this->createChildField(
            'MECH_AERO',
            'Aerospace Engineering',
            $mechanical->id,
            'Aerospace systems and aeronautical engineering',
            4
        );

        // Level 2 - Sub-specializations under EEE_BME
        $this->createChildField(
            'EEE_BME_MD',
            'Medical Devices & Instrumentation',
            $eeeBme->id,
            'Design and development of medical devices and diagnostic equipment',
            1,
            2
        );

        $this->createChildField(
            'EEE_BME_BI',
            'Biomedical Imaging',
            $eeeBme->id,
            'Medical imaging modalities and signal processing',
            2,
            2
        );

        $this->createChildField(
            'EEE_BME_REHAB',
            'Rehabilitation Engineering',
            $eeeBme->id,
            'Assistive technologies and rehabilitation systems',
            3,
            2
        );

        // Level 2 - Sub-specializations under IT_SW
        $this->createChildField(
            'IT_SW_WEB',
            'Web Development',
            $itSoftware->id,
            'Frontend and backend web application development',
            1,
            2
        );

        $this->createChildField(
            'IT_SW_MOBILE',
            'Mobile Application Development',
            $itSoftware->id,
            'Cross-platform and native mobile app development',
            2,
            2
        );

        $this->createChildField(
            'IT_SW_DEVOPS',
            'DevOps & Cloud Engineering',
            $itSoftware->id,
            'CI/CD pipelines, containerization and cloud infrastructure',
            3,
            2
        );

        $this->command?->info('Field taxonomy seeded successfully.');
    }

    /**
     * Create a root field (level 0).
     */
    private function createRootField(
        string $code,
        string $name,
        string $description,
        int $displayOrder
    ): FieldTaxonomy {
        return FieldTaxonomy::updateOrCreate(
            ['code' => $code],
            [
                'name'          => $name,
                'parent_id'     => null,
                'level'         => 0,
                'description'   => $description,
                'is_active'     => true,
                'display_order' => $displayOrder,
            ]
        );
    }

    /**
     * Create a child field (level 1 or 2).
     */
    private function createChildField(
        string $code,
        string $name,
        int $parentId,
        string $description,
        int $displayOrder,
        int $level = 1
    ): FieldTaxonomy {
        return FieldTaxonomy::updateOrCreate(
            ['code' => $code],
            [
                'name'          => $name,
                'parent_id'     => $parentId,
                'level'         => $level,
                'description'   => $description,
                'is_active'     => true,
                'display_order' => $displayOrder,
            ]
        );
    }
}
