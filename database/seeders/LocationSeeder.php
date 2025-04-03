<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            ['city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['city' => 'London', 'state' => null, 'country' => 'UK'],
            ['city' => 'Berlin', 'state' => null, 'country' => 'Germany'],
            ['city' => 'Tokyo', 'state' => null, 'country' => 'Japan'],
            ['city' => 'Remote', 'state' => null, 'country' => 'Global'],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
