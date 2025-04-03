<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'years_experience',
                'type' => 'number',
                'options' => null
            ],
            [
                'name' => 'education_level',
                'type' => 'select',
                'options' => json_encode(['High School', 'Bachelor', 'Master', 'PhD'])
            ],
            [
                'name' => 'security_clearance',
                'type' => 'boolean',
                'options' => null
            ],
            [
                'name' => 'start_date',
                'type' => 'date',
                'options' => null
            ],
            [
                'name' => 'project_size',
                'type' => 'select',
                'options' => json_encode(['Small', 'Medium', 'Large', 'Enterprise'])
            ],
        ];

        foreach ($attributes as $attribute) {
            Attribute::create($attribute);
        }
    }
}
