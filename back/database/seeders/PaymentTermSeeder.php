<?php

namespace Database\Seeders;

use App\Models\PaymentTerm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'name' => 'Anticipo'
            ],
            [
                'id' => 2,
                'name' => 'Crédito o Financiamiento'
            ],
            [
                'id' => 3,
                'name' => 'Anticipo + Financiamiento'
            ],
            [
                'id' => 4,
                'name' => 'Anticipo + Contra Embarque & Fin.'
            ],
            [
                'id' => 5,
                'name' => 'Contra entrega'
            ],
        ];

        PaymentTerm::insert($data);
    }
}
