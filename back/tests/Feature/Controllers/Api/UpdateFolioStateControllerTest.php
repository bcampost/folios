<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Folio;
use App\Models\Project;
use Tests\FeatureTestCase;
use App\Enums\FolioTypeEnum;
use App\States\Folio\RechazadoState;
use App\States\Folio\CostoAsignadoState;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\FolioSolicitadoState;
use App\States\Folio\PrevioSolicitadoState;
use Illuminate\Foundation\Testing\WithFaker;
use App\States\Folio\NumeroFolioAsignadoState;
use App\States\Folio\LiberadoParaFacturarState;
use App\States\Folio\PrecioDeListaAsignadoState;
use App\States\Folio\LiberadoParaProduccionState;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateFolioStateControllerTest extends FeatureTestCase
{
    /**
     * @test
     */
    public function test_folio_state_can_be_updated(): void
    {
        $this->withoutExceptionHandling();

        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => PrevioSolicitadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => PrevioAprobadoState::getStateId()
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(PrevioAprobadoState::class, $folio->state);
    }

    /**
     * @test
     */
    public function test_folio_state_can_be_update_from_aprobado_to_costo_asignado(): void
    {
        $this->withoutExceptionHandling();

        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => PrevioAprobadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => CostoAsignadoState::getStateId(),
            'cost'  => 10.50,
            'cost_details'  => 'test cost details'
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(CostoAsignadoState::class, $folio->state);
        $this->assertEquals(10.50, $folio->cost);
        $this->assertEquals('test cost details', $folio->cost_details);
    }

    /**
     * @test
     */
    public function test_folio_state_can_be_update_from_costo_asignado_to_precio_de_lista_asignado(): void
    {

        $this->withoutExceptionHandling();

        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => CostoAsignadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => PrecioDeListaAsignadoState::getStateId(),
            'list_price'  => 20,
            'list_price_details'  => 'test list price details'
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(PrecioDeListaAsignadoState::class, $folio->state);
        $this->assertEquals(20, $folio->list_price);
        $this->assertEquals('test list price details', $folio->list_price_details);
    }

    /**
     * @test
     */
    public function test_folio_state_can_be_update_to_rechazado(): void
    {
        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => PrevioAprobadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => RechazadoState::getStateId(),
            'reason_for_rejection'  => 'test'
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(RechazadoState::class, $folio->state);
        $this->assertEquals('test', $folio->reason_for_rejection);

        $transition = $folio->transitions()->first();
        $this->assertEquals(PrevioAprobadoState::getStateId(), $transition->prev_state_id);
        $this->assertEquals(RechazadoState::getStateId(), $transition->next_state_id);
    }

    /**
     * @test
     */
    public function test_folio_is_deleted_when_updating_state_from_previo_solicitado_to_rechazado(): void
    {
        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => PrevioSolicitadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => RechazadoState::getStateId(),
            'reason_for_rejection'  => 'test'
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseMissing('folios', ['id' => $folio->id]);
    }

    /**
     * @test
     */
    public function test_folio_is_deleted_and_assembly_siblings_are_deleted_when_updating_state_from_previo_solicitado_to_rechazado(): void
    {
        $this->asSuperAdmin();

        $project = Project::factory()->create();
        $project->folios()->saveMany(Folio::factory(3)->create([
            'state' => PrevioSolicitadoState::getStateId(),
            'assembly_number' => 1
        ]));

        $folios = $project->folios;

        $response = $this->postJson(route('folios.state.update', $folios[0]), [
            'state_id' => RechazadoState::getStateId(),
            'reason_for_rejection'  => 'test'
        ]);

        $response->assertSuccessful();

        foreach ($folios as $folio) {
            $this->assertDatabaseMissing('folios', ['id' => $folio->id]);
        }
    }

    /**
     * @test
     */
    public function test_folio_and_assembly_siblings_are_updated_to_rechazado(): void
    {
        $this->asSuperAdmin();

        $project = Project::factory()->create();
        $project->folios()->saveMany(Folio::factory(3)->create([
            'state' => PrevioAprobadoState::getStateId(),
            'assembly_number' => 1
        ]));

        $folios = $project->folios;

        $response = $this->postJson(route('folios.state.update', $folios[0]), [
            'state_id' => RechazadoState::getStateId(),
            'reason_for_rejection'  => 'rejected reason test'
        ]);

        $response->assertSuccessful();

        foreach ($folios as $folio) {
            $folio->refresh();
            $this->assertEquals(RechazadoState::getStateId(), $folio->state::getStateId());
        }
    }

    /**
     * @test
     */
    public function test_folio_state_can_be_update_from_costo_asignado_to_folio_solicitado(): void
    {
        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => CostoAsignadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => FolioSolicitadoState::getStateId(),
            'melamina_color'  => 'Blanco',
            'chapacinta_color'  => 'Blanco',
            'tela_color'  => 'Blanco',
            'structure_color'  => 'Blanco',
            'acabados'  => 'acabados test'
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(FolioSolicitadoState::class, $folio->state);
        $this->assertEquals(FolioTypeEnum::Folio, $folio->type);
    }

    public function test_folio_state_can_be_update_from_precio_de_lista_asignado_to_folio_solicitado(): void
    {
        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => PrecioDeListaAsignadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => FolioSolicitadoState::getStateId(),
            'melamina_color'  => 'Blanco',
            'chapacinta_color'  => 'Blanco',
            'tela_color'  => 'Blanco',
            'structure_color'  => 'Blanco',
            'acabados'  => 'acabados test'
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(FolioSolicitadoState::class, $folio->state);
        $this->assertEquals(FolioTypeEnum::Folio, $folio->type);
        $this->assertEquals('acabados test', $folio->acabados);
        $this->assertEquals('Blanco', $folio->melamina_color);
    }


    /**
     * @test
     */
    public function test_folio_state_can_be_update_from_folio_solicitado_to_aprobado(): void
    {
        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => FolioSolicitadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => FolioAprobadoState::getStateId()
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(FolioAprobadoState::class, $folio->state);
    }

    /**
     * @test
     */
    public function test_folio_state_can_be_update_from_folio_aprobado_to_numero_folio_asignado(): void
    {
        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => FolioAprobadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => NumeroFolioAsignadoState::getStateId(),
            'folio_code' => '123456'
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(NumeroFolioAsignadoState::class, $folio->state);
        $this->assertEquals('123456', $folio->folio_code);
    }

    /**
     * @test
     */
    public function test_folio_state_can_be_update_from_folio_aprobado_to_liberado_para_facturar(): void
    {
        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => FolioAprobadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => LiberadoParaFacturarState::getStateId()
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(LiberadoParaFacturarState::class, $folio->state);
    }

    /**
     * @test
     */
    public function test_folio_state_can_be_update_from_liberado_para_facturar_to_produccion(): void
    {
        $this->withoutExceptionHandling();

        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => LiberadoParaFacturarState::getStateId()
        ]);

        $response = $this->postJson(route('folios.state.update', $folio), [
            'state_id' => LiberadoParaProduccionState::getStateId()
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(LiberadoParaProduccionState::class, $folio->state);
    }
}
