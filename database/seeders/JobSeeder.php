<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobAttributeValue;
use App\Models\Language;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class JobSeeder extends Seeder
{
    public function run()
    {
        $jobs = [
            [
                'title' => 'Senior PHP Developer',
                'description' => 'We are looking for an experienced PHP developer to join our team.',
                'company_name' => 'Tech Corp Inc.',
                'salary_min' => 80000,
                'salary_max' => 120000,
                'is_remote' => true,
                'job_type' => 'full-time',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'title' => 'JavaScript Engineer',
                'description' => 'Join our frontend team to build amazing user experiences.',
                'company_name' => 'Web Solutions LLC',
                'salary_min' => 90000,
                'salary_max' => 130000,
                'is_remote' => false,
                'job_type' => 'full-time',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'title' => 'DevOps Specialist',
                'description' => 'Help us improve our deployment pipeline and infrastructure.',
                'company_name' => 'Cloud Services Co.',
                'salary_min' => 100000,
                'salary_max' => 140000,
                'is_remote' => true,
                'job_type' => 'contract',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'title' => 'Junior Python Developer',
                'description' => 'Entry-level position for Python enthusiasts.',
                'company_name' => 'Data Analytics Ltd.',
                'salary_min' => 60000,
                'salary_max' => 80000,
                'is_remote' => false,
                'job_type' => 'full-time',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'title' => 'Mobile App Developer (React Native)',
                'description' => 'Build cross-platform mobile applications with us.',
                'company_name' => 'App Creators Inc.',
                'salary_min' => 85000,
                'salary_max' => 110000,
                'is_remote' => true,
                'job_type' => 'full-time',
                'status' => 'published',
                'published_at' => now(),
            ],
        ];

        $languages = Language::all();
        $locations = Location::all();
        $categories = Category::all();
        $attributes = Attribute::all();

        foreach ($jobs as $jobData) {
            $job = Job::create($jobData);

            $job->languages()->attach(
                $languages->random(rand(2, 4))->pluck('id')
            );

            $job->locations()->attach(
                $locations->random(rand(1, 3))->pluck('id')
            );

            $job->categories()->attach(
                $categories->random(rand(1, 2))->pluck('id')
            );

            foreach ($attributes as $attribute) {
                $value = $this->generateAttributeValue($attribute);
                JobAttributeValue::create([
                    'job_id' => $job->id,
                    'attribute_id' => $attribute->id,
                    'value' => $value
                ]);
            }
        }
    }

    protected function generateAttributeValue($attribute)
    {
        switch ($attribute->type) {
            case 'number':
                return rand(1, 10);
            case 'select':
                $options = json_decode($attribute->options);
                return $options[array_rand($options)];
            case 'boolean':
                return rand(0, 1) ? 'true' : 'false';
            case 'date':
                return Carbon::now()->addDays(rand(1, 30))->format('Y-m-d');
            default:
                return 'Sample text value';
        }
    }
}
