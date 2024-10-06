<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(database_path('data/all-province.json'));
        $provinces = json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Invalid JSON file');
            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('provinces')->truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($provinces as $province) {
            DB::table('provinces')->insert([
                'name' => $province->name,
                'code' => $province->code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Provinces seeded successfully');
    }
}
