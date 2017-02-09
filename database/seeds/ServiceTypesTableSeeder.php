<?php

use Illuminate\Database\Seeder;

use Carbon\Carbon;

class ServiceTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('service_types')->truncate();
        DB::table('service_types')->insert([
            [
                'name' => 'Sedan',
                'provider_name' => 'Driver',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Hatchback',
                'provider_name' => 'Driver',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'SUV',
                'provider_name' => 'Driver',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Limousine',
                'provider_name' => 'Driver',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
