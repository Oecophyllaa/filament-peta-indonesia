<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $provinceIds = DB::table('provinces')->pluck('id')->toArray();

        $users = [];

        for ($x = 0; $x < 500; $x++) {
            $users[] = [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password'),
                'province_id' => $faker->randomElement($provinceIds),
                'age' => $faker->numberBetween(18, 60),
                'gender' => $faker->randomElement(['M', 'F']),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (($x + 1) % 50 === 0) {
                DB::table('users')->insert($users);
                $users = [];
            }
        }

        if (!empty($users)) {
            DB::table('users')->insert($users);
        }

        $this->command->info('Users seeded successfully');
    }
}
