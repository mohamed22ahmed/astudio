<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Web Development'],
            ['name' => 'Mobile Development'],
            ['name' => 'Data Science'],
            ['name' => 'DevOps'],
            ['name' => 'UI/UX Design'],
            ['name' => 'Product Management'],
            ['name' => 'Quality Assurance'],
            ['name' => 'Technical Writing'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
