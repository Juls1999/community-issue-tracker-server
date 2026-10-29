<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'resident',
                'description' => 'Community member who reports issues.',
            ],
            [
                'name' => 'staff',
                'description' => 'Staff member who handles reports.',
            ],
            [
                'name' => 'admin',
                'description' => 'Administrator who manages the system.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}