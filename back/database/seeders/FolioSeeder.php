<?php

namespace Database\Seeders;

use App\Models\Folio;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Folio::factory(50)->create();
    }
}
