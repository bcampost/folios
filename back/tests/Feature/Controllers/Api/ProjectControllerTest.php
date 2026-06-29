<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Deal;
use App\Enums\RoleEnum;
use App\Models\Product;
use App\Models\Project;
use Tests\FeatureTestCase;
use App\Enums\FolioTypeEnum;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\FolioSolicitadoState;
use App\States\Folio\PrevioSolicitadoState;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectControllerTest extends FeatureTestCase
{
    /**
     * @test
     */
    public function test_project_can_be_store(): void
    {
        $this->withoutExceptionHandling();

        $this->asSuperAdmin();

        $product = Product::first();
        $deal = Deal::first();

        $data = [
            'value' => '0 - 500K MXN',
            // 'deal_id' => $deal->id,
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

        $this->assertDatabaseHas('projects', [
            'value' => '0 - 500K MXN',
            // 'deal_id' => $deal->id,
            'deal_id' => null,
            'channel' => 'Retail',
            'owner_id' => $this->getUser()->id
        ]);

        $project = Project::latest('id')->first();

        $folio = $project->folios()->first();

        $this->assertNotNull($folio);

        $this->assertDatabaseHas('folios', [
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
            'reference_product' => $product->id,
            'project_id' => $project->id,
            // 'state'=> PrevioAprobadoState::getStateId()
            'state' => FolioAprobadoState::getStateId()
        ]);
    }

    /**
     * @test
     */
    public function test_advisor_can_create_project(): void
    {
        $this->withoutExceptionHandling();

        $this->asRole(RoleEnum::Advisor);

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
                    'reference_product' => $product->id
                ]
            ],
        ];

        $response = $this->postJson(route('projects.store'), $data);

        $response->assertCreated();

        $this->assertDatabaseHas('projects', [
            'value' => '0 - 500K MXN',
            'deal_id' => $deal->id,
            'channel' => 'Retail',
            'owner_id' => $this->getUser()->id
        ]);

        $project = Project::latest('id')->first();

        $folio = $project->folios()->first();

        $this->assertNotNull($folio);

        $this->assertDatabaseHas('folios', [
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
            'reference_product' => $product->id,
            'project_id' => $project->id,
            // 'state'=> PrevioAprobadoState::getStateId()
            'state'=> FolioSolicitadoState::getStateId(),
            'type' => FolioTypeEnum::Folio->value
        ]);
    }

     /**
     * @test
     */
    public function test_project_with_assembly_can_be_store(): void
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
                    'reference_product' => $product->id,
                    'assembly_number' => 1
                ],
                [
                    'width' => 20,
                    'height' => 30,
                    'depth' => 40,
                    'quantity' => 2,
                    'classification' => 'B',
                    'melamina_color' => 'rojo',
                    'melamina_density' => 100,
                    'chapacinta_color' => 'rojo',
                    'structure_color' => 'rojo',
                    'tela_color' => 'rojo',
                    'package_type' => 'Linea',
                    'description' => 'test',
                    'reference_product' => $product->id,
                    'assembly_number' => 1
                ],
            ],
        ];

        $response = $this->postJson(route('projects.store'), $data);

        $response->assertCreated();

        $this->assertDatabaseHas('projects', [
            'value' => '0 - 500K MXN',
            'deal_id' => $deal->id,
            'channel' => 'Retail',
            'owner_id' => $this->getUser()->id
        ]);

        $project = Project::latest('id')->first();

        $folio = $project->folios()->first();

        $this->assertNotNull($folio);

        $this->assertDatabaseHas('folios', [
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
            'reference_product' => $product->id,
            'project_id' => $project->id,
            'assembly_number' => 1
        ]);
    }

    /**
     * @test
     */
    public function test_project_with_classification_d_can_be_store(): void
    {
        $this->asSuperAdmin();

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
                    'classification' => 'D',
                    'melamina_color' => 'rojo',
                    'melamina_density' => 100,
                    'chapacinta_color' => 'rojo',
                    'structure_color' => 'rojo',
                    'tela_color' => 'rojo',
                    'package_type' => 'Linea',
                    'description' => 'test'
                ]
            ],
        ];

        $response = $this->postJson(route('projects.store'), $data);

        $response->assertCreated();

        $this->assertDatabaseHas('projects', [
            'value' => '0 - 500K MXN',
            'deal_id' => $deal->id,
            'channel' => 'Retail',
            'owner_id' => $this->getUser()->id
        ]);

        $project = Project::latest('id')->first();

        $folio = $project->folios()->first();

        $this->assertNotNull($folio);

        $this->assertDatabaseHas('folios', [
            'width' => 100,
            'height' => 80,
            'depth' => 50,
            'quantity' => 2,
            'classification' => 'D',
            'melamina_color' => 'rojo',
            'melamina_density' => 100,
            'chapacinta_color' => 'rojo',
            'structure_color' => 'rojo',
            'tela_color' => 'rojo',
            'package_type' => 'Linea',
            'description' => 'test',
            'project_id' => $project->id
        ]);
    }
}
