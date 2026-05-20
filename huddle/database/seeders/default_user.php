<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserFlags;
use Illuminate\Support\Facades\Hash;

class default_user extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserFlags::create([
            'name' => 'Committee',
            'description' => 'Committee member',
        ], [
            'name' => 'Trustee',
            'description' => 'Trustee'
        ],
        [
            'name' => 'Keyholder',
            'description' => 'Keyholder',
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@huddle.skullfire.co.uk',
            'password' => Hash::make('password'),
            'role_id' => 1,
        ]);


    }
}
