<?php

namespace Database\Seeders;

use App\Support\MockData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = MockData::orderRequests();

        if ($rows === []) {
            return;
        }

        DB::table('order_requests')->insert($rows);
    }
}
