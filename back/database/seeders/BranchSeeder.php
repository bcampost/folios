<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'CDMX'
            ],
            [
                'name' => 'AGS'
            ],
            [
                'name' => 'MTY'
            ],
            [
                'name' => 'QRO'
            ],
        ];

        Branch::insert($data);
    }
}
