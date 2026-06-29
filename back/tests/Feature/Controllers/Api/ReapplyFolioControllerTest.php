<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Deal;
use App\Models\Folio;
use App\Models\Product;
use App\Models\Project;
use Tests\FeatureTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReapplyFolioControllerTest extends FeatureTestCase
{
    /**
     * @test
     */
    public function test_folio_can_be_reapplied(): void
    {
        $this->withoutExceptionHandling();

        $this->asSuperAdmin();

        $product = Product::first();
        $deal = Deal::first();

        $data = [
            'value' => '0 - 500K MXN',
            'deal_id' => $deal->id,
            'channel' => 'Retail',
            'payment_term_id' => 1,
            'modality' => 'Anticipo 100%',
            'payment_by_customer_platform' => true,
            'discount' => 0,
            'previos' => [
                [
                    'width' => 100,
                    'height' => 80,
                    'depth' => 50,
                    'quantity' => 2,
                    'classification' => 'A',
                    'melamina_color' => 'rojo',
                    'melamina_density' => 100,
                    'chapacinta_color' => 'rojo',
                    'structure_color' => 'rojo',
                    'tela_color' => 'rojo',
                    'package_type' => 'Linea',
                    'description' => 'test',
                    'acabados' => 'acabados test',
                    'reference_product' => $product->id
                ]
            ],
        ];

        $response = $this->postJson(route('projects.store'), $data);

        $response->assertCreated();

        $project = Project::first();
        $folio = $project->folios()->first();

        $folioData = [
            'width' => 120,
            'height' => 80,
            'depth' => 50,
            'quantity' => 2,
            'classification' => 'A',
            'melamina_color' => 'rojo',
            'melamina_density' => 100,
            'chapacinta_color' => 'rojo',
            'structure_color' => 'rojo',
            'tela_color' => 'rojo',
            'package_type' => 'Linea',
            'description' => 'test',
            'acabados' => 'acabados test',
        ];

        $response = $this->postJson(route('folios.reapply', $folio), $folioData);

        $folio = Folio::latest('id')->first();

        $this->assertEquals($folioData['width'], $folio->width);
    }
}
