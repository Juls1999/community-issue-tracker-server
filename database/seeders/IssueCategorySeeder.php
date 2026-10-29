<?php

namespace Database\Seeders;

use App\Models\IssueCategory;
use Illuminate\Database\Seeder;

class IssueCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Road Damage',
                'description' => 'Potholes and damaged roads.',
            ],
            [
                'name' => 'Waste Management',
                'description' => 'Garbage collection and waste concerns.',
            ],
            [
                'name' => 'Drainage and Flooding',
                'description' => 'Blocked drainage and flooding.',
            ],
            [
                'name' => 'Street Lighting',
                'description' => 'Broken or nonfunctional streetlights.',
            ],
            [
                'name' => 'Public Facilities',
                'description' => 'Damaged or poorly maintained public facilities.',
            ],
            [
                'name' => 'Other',
                'description' => 'Community issues not covered by other categories.',
            ],
        ];

        foreach ($categories as $category) {
            IssueCategory::updateOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}