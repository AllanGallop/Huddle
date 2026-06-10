<?php

namespace Database\Seeders;

use App\Models\UserFlags;
use Illuminate\Database\Seeder;

class default_user extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Committee', 'description' => 'Committee member'],
            ['name' => 'Trustee', 'description' => 'Trustee'],
            ['name' => 'Keyholder', 'description' => 'Keyholder'],
            ['name' => 'Mentor', 'description' => 'Accreditation Mentor'],
        ] as $flag) {
            UserFlags::firstOrCreate(['name' => $flag['name']], ['description' => $flag['description']]);
        }

    }
}
