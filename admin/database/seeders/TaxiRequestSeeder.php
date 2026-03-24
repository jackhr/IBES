<?php

namespace Database\Seeders;

use App\Support\MockData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxiRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = MockData::taxiRequests();

        if ($rows === []) {
            return;
        }

        DB::table('taxi_requests')->insert($rows);
    }
}
