<?php

namespace Tests\Feature\Controllers\Api;

use App\Mail\PrevioAprobado;
use App\Models\Deal;
use App\Models\Folio;
use App\Models\Product;
use App\Models\Project;
use Tests\FeatureTestCase;
use App\States\Folio\CostoAsignadoState;
use App\States\Folio\PrevioAprobadoState;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReturnToApprovedControllerTest extends FeatureTestCase
{
    /**
     * @test
     */
    public function test_folio_can_be_return_to_approved(): void
    {
        $this->withoutExceptionHandling();

        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'state' => CostoAsignadoState::getStateId()
        ]);

        $response = $this->postJson(route('folios.return-to-approved', $folio), [
            'comments'  => 'test comment'
        ]);

        $folio->refresh();

        $response->assertSuccessful();

        $this->assertInstanceOf(PrevioAprobadoState::class, $folio->state);

        $this->assertEquals('test comment', $folio->comments);

        $transition = $folio->lastTransition;

        $this->assertEquals($transition->prev_state_id, CostoAsignadoState::getStateId());
        $this->assertEquals($transition->next_state_id, PrevioAprobadoState::getStateId());
    }
}
