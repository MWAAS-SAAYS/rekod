<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\JobGradeSeeder;
use Database\Seeders\FieldTaxonomySeeder;
use Database\Seeders\LetterTemplateSeeder;
use Database\Seeders\DistressEscalationRuleSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RoleSeeder::class);

        $this->call(UsersSeeder::class);

        $this->call(JobGradeSeeder::class);

        $this->call(FieldTaxonomySeeder::class);

        $this->call(LetterTemplateSeeder::class);

        $this->call(DistressEscalationRuleSeeder::class);
    }

}
