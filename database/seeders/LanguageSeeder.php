<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['name' => 'PHP'],
            ['name' => 'JavaScript'],
            ['name' => 'Python'],
            ['name' => 'Java'],
            ['name' => 'C#'],
            ['name' => 'Ruby'],
            ['name' => 'Go'],
            ['name' => 'TypeScript'],
            ['name' => 'Swift'],
            ['name' => 'Kotlin'],
        ];

        foreach ($languages as $language) {
            Language::create($language);
        }
    }
}
