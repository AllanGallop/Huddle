<?php

namespace Database\Seeders;

use App\Models\ProjectCategory;
use Illuminate\Database\Seeder;

class ProjectCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Woodshop', 'description' => 'Woodworking and joinery projects'],
            ['name' => 'H&S', 'description' => 'Health and safety'],
            ['name' => 'Metalwork', 'description' => 'Metal fabrication and welding'],
            ['name' => 'Electronics', 'description' => 'Electronics and electrics'],
            ['name' => 'Community', 'description' => 'Community outreach and events'],
            ['name' => 'Maintenance', 'description' => 'Building and equipment maintenance'],
        ] as $category) {
            ProjectCategory::query()->firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']],
            );
        }
    }
}
