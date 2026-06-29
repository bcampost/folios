<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Folio;
use Tests\FeatureTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FolioActivityControllerTest extends FeatureTestCase
{
    /**
     * @test
     */
    public function test_get_folio_activities_logs(): void
    {
        $this->withoutExceptionHandling();

        $this->asSuperAdmin();

        $folio = Folio::factory()->create();

        $folio->update([
            'previo_code' => '123456'
        ]);

        $response = $this->getJson(route('folios.activities', $folio));
        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                [
                    'old',
                    'new',
                    'causer'
                ]
            ]
        ]);
    }
}
