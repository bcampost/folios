<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Folio;
use App\Models\Product;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class FolioExportController extends Controller
{
    public function export(Request $request)
    {
        $dealsTable = app(Deal::class)->getTable();
        $productsTable = app(Product::class)->getTable();

        // Construir el query usando el mismo QueryBuilder del método index
        $folios = QueryBuilder::for(Folio::class)
            ->select(
                'folios.*',
                'deals.name as deal_name',
                'products.sku as product_sku',
            )
            ->with([
                'project.deal.customer',
                'owner.branch',
                'transitions' => function ($query) {
                    $query->where('next_state_id', 7); // FolioAprobadoState
                }
            ])
            ->leftJoin("{$productsTable} as products", 'products.id', '=', 'folios.reference_product')
            ->leftJoin('projects', 'folios.project_id', '=', 'projects.id')
            ->leftJoin('users', 'projects.owner_id', '=', 'users.id')
            ->leftJoin("{$dealsTable} as deals", 'deals.id', '=', 'projects.deal_id')
            ->when(
                ! auth()->user()->hasFullAccess(),
                function ($query) {
                    $role = auth()->user()->role;

                    if ($role === RoleEnum::Advisor) {
                        $query->where('projects.owner_id', auth()->user()->id);
                    }

                    if ($role === RoleEnum::Admin) {
                        $query->where(function ($query) {
                            $query->whereIn('users.branch_id', auth()->user()->branches()->pluck('branches.id')->toArray())
                                ->orWhere('projects.owner_id', auth()->user()->id);
                        });
                    }

                    if ($role === RoleEnum::TeamLeader) {
                        $query->where(function ($query) {
                            $query->where('projects.owner_id', auth()->user()->id)
                                ->orWhereIn(
                                    'projects.owner_id',
                                    auth()->user()->assignedUsers()->pluck('users.id')->toArray()
                                );
                        });
                    }
                }
            )
            ->allowedFilters([
                AllowedFilter::exact('classification', 'folios.classification'),
                AllowedFilter::callback('state', function ($query, $value) {
                    if (is_array($value)) {
                        $query->whereIn('folios.state', $value);
                    } else {
                        $query->where('folios.state', $value);
                    }
                }),
                AllowedFilter::exact('type'),
                AllowedFilter::callback('search', function ($query, $value) use ($dealsTable) {
                    $query->where(function ($query) use ($value, $dealsTable) {
                        $query->where("{$dealsTable}.name", 'like', '%' . $value . '%')
                            ->orWhere('folios.previo_code', 'like', '%' . $value . '%')
                            ->orWhere('folios.folio_code', 'like', '%' . $value . '%')
                            ->orWhere('users.name', 'like', '%' . $value . '%')
                            ->orWhere('products.sku', 'like', '%' . $value . '%');
                    });
                }),
                AllowedFilter::callback('product_sku', function ($query, $value) {
                    $query->whereHas('product', function ($query) use ($value) {
                        $query->where('sku', 'like', '%' . $value . '%');
                    });
                }),
            ])
            ->allowedSorts([
                'classification',
                AllowedSort::field('cost', 'folios.cost'),
                AllowedSort::field('list_price', 'folios.list_price'),
                AllowedSort::field('updated_at', 'folios.updated_at'),
                AllowedSort::field('created_at', 'folios.created_at'),
                AllowedSort::field('value', 'projects.value'),
                AllowedSort::field('deal_name', 'deals.name'),
                AllowedSort::field('owner', 'users.name'),
            ])
            ->defaultSort('-folios.updated_at', '-folios.created_at')
            ->get();

        // Crear el archivo Excel con streaming
        $writer = SimpleExcelWriter::streamDownload('Reporte_Folios_' . now()->format('Y-m-d_H-i-s') . '.xlsx');

        // Agregar datos
        $rowCount = 0;
        foreach ($folios as $folio) {
            // Obtener la fecha de aprobación (transition con next_state_id = 7)
            $fechaAprobacion = null;
            if ($folio->transitions && $folio->transitions->isNotEmpty()) {
                $transitionAprobado = $folio->transitions->first();
                $fechaAprobacion = $transitionAprobado ? $transitionAprobado->created_at->format('d-M-y') : null;
            }

            // Obtener el mes de created_at
            $mes = $folio->created_at ? $folio->created_at->locale('es')->translatedFormat('F') : null;

            $writer->addRow([
                'PREVIO' => $folio->previo_code ?? '',
                'FOLIO' => $folio->folio_code ?? '',
                'VENDEDOR' => $folio->owner ? $folio->owner->name : '',
                'FECHA DE FOLIO SOLICITADO' => $folio->created_at ? $folio->created_at->format('d-M-y') : '',
                'FECHA DE FOLIO APROBADO' => $fechaAprobacion ?? '',
                'ESTATUS' => 'SIN PEDIDO',
                'SUCURSAL' => $folio->owner && $folio->owner->branch ? $folio->owner->branch : '',
                'CLIENTE' => $folio->project && $folio->project->deal && $folio->project->deal->dealable()
                    ? $folio->project?->deal?->dealable()?->company_name
                    : '',
                'DESCRIPCIÓN' => $folio->description ?? '',
                'PRECIO UNITARIO' => $folio->list_price ?? '',
                'PRECIO TENTATIVO DEL PRODUCTO' => $folio->list_price ? $folio->list_price * $folio->quantity : '',
                'RAZÓN DE CANCELACIÓN' => $folio->reason_for_rejection ?? '',
                'MES' => $mes ?? '',
            ]);

            $rowCount++;

            // Flush cada 1000 filas para optimizar memoria
            if ($rowCount % 1000 === 0) {
                flush();
            }
        }

        return $writer->toBrowser();
    }
}
