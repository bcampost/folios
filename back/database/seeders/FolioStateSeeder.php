<?php

namespace Database\Seeders;

use App\Models\FolioState;
use Illuminate\Database\Seeder;
use App\States\Folio\RechazadoState;
use App\States\Folio\CostoAsignadoState;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\FolioSolicitadoState;
use App\States\Folio\PrevioSolicitadoState;
use App\States\Folio\NumeroFolioAsignadoState;
use App\States\Folio\LiberadoParaFacturarState;
use App\States\Folio\PrecioDeListaAsignadoState;
use App\States\Folio\LiberadoParaProduccionState;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FolioStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => PrevioSolicitadoState::getStateId(),
                'title' => 'Previo Solicitado'
            ],
            [
                'id' => PrevioAprobadoState::getStateId(),
                'title' => 'Previo Aprobado'
            ],
            [
                'id' => CostoAsignadoState::getStateId(),
                'title' => 'Costo Asignado'
            ],
            [
                'id' => PrecioDeListaAsignadoState::getStateId(),
                'title' => 'Precio de Lista Asignado'
            ],
            [
                'id' => RechazadoState::getStateId(),
                'title' => 'Rechazado'
            ],
            [
                'id' => FolioSolicitadoState::getStateId(),
                'title' => 'Folio Solicitado'
            ],
            [
                'id' => FolioAprobadoState::getStateId(),
                'title' => 'Folio Aprobado'
            ],
            [
                'id' => NumeroFolioAsignadoState::getStateId(),
                'title' => 'Número de Folio Asignado'
            ],
            [
                'id' => LiberadoParaFacturarState::getStateId(),
                'title' => 'Liberado para Facturar'
            ],
            [
                'id' => LiberadoParaProduccionState::getStateId(),
                'title' => 'Liberado para Producción'
            ],

        ];

        foreach ($data as $key => $value) {
            FolioState::firstOrCreate($value);
        }
    }
}
