<?php

namespace Database\Seeders;

use App\Models\Record;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        $data = [];
       
        foreach(range(0,20000) as $index) {
            $data[] = [
                'name' => $faker->name(),
                'bio' => $faker->address(),
                'age' => $faker->numberBetween(10,100),
                'contact' =>  $faker->e164PhoneNumber(),
                'is_active' => $faker->boolean(),
                'born_on' => $faker->dateTime()->format("Y-m-d H:i:s"),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        foreach(array_chunk($data,4000) as &$new) {
            Record::insert($new);
        }

        unset($data);
        
    }
}
