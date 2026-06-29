<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\User;
use App\Models\Folio;
use Carbon\Carbon;
use App\Enums\RoleEnum;
use App\Models\Branch;
use App\Models\Project;
use Tests\FeatureTestCase;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\LiberadoParaFacturarState;
use App\States\Folio\LiberadoParaProduccionState;

class FolioControllerTest extends FeatureTestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function test_can_get_folios(): void
    {
        $this->asSuperAdmin();

        $response = $this->getJson(route('folios.index'));

        $response->assertOk();

        $this->assertGreaterThan(0, $response->json('data'));
    }

    /**
     * @test
     */
    public function test_advisor_can_only_see_his_folios(): void
    {
        $advisor = User::factory()->create([
            'role' => RoleEnum::Advisor
        ]);

        $this->signIn($advisor);

        $project = Project::factory()->create([
            'owner_id' => $advisor->id
        ]);

        $folio = Folio::factory()->create([
            'project_id' => $project->id
        ]);

        $response = $this->getJson(route('folios.index'));

        $response->assertOk();

        $this->assertCount(1, $response->json('data'));
    }

    /**
     * @test
     */
    public function test_admin_can_only_see_folios_of_his_branch(): void
    {
        $branch = Branch::first();

        $admin = User::factory()->create([
            'role' => RoleEnum::Admin,
            'branch_id' => $branch->id
        ]);

        $this->signIn($admin);

        $user1 = User::factory()->create([
            'role' => RoleEnum::Advisor,
            'branch_id' => $branch->id
        ]);

        $project1 = Project::factory()->create([
            'owner_id' => $user1->id
        ]);

        $folio1 = Folio::factory()->create([
            'project_id' => $project1->id
        ]);

        $response = $this->getJson(route('folios.index'));

        $response->assertOk();

        // Loop through the response data to check if the owner.branch_id is 1
        foreach ($response->json('data') as $folio) {
            $this->assertEquals($admin->branch_id, $folio['owner']['branch_id']);
            $this->assertEquals('advisor', $folio['owner']['role']);
        }
    }

    /**
     * @test
     */
    public function test_folio_index_includes_on_time_delivery_timing_for_in_progress_folio(): void
    {
        Carbon::setTestNow('2026-05-21 12:00:00');

        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'classification' => 'C',
            'state' => LiberadoParaFacturarState::getStateId(),
        ]);

        $folio->transitions()->create([
            'user_id' => $this->getUser()->id,
            'prev_state_id' => 6,
            'next_state_id' => FolioAprobadoState::getStateId(),
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $folio->transitions()->create([
            'user_id' => $this->getUser()->id,
            'prev_state_id' => FolioAprobadoState::getStateId(),
            'next_state_id' => LiberadoParaFacturarState::getStateId(),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->getJson(route('folios.index'));

        $response->assertOk();

        $record = collect($response->json('data'))->firstWhere('id', $folio->id);

        $this->assertNotNull($record);
        $this->assertSame(4, $record['delivery_timing']['target_days']);
        $this->assertSame(3, $record['delivery_timing']['elapsed_days']);
        $this->assertSame('3 dias', $record['delivery_timing']['elapsed_label']);
        $this->assertSame('on_time', $record['delivery_timing']['status']);
        $this->assertFalse($record['delivery_timing']['is_completed']);
        $this->assertFalse($record['delivery_timing']['is_late']);
    }

    /**
     * @test
     */
    public function test_folio_index_includes_completed_late_delivery_timing_for_finished_folio(): void
    {
        Carbon::setTestNow('2026-05-21 12:00:00');

        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'classification' => 'B',
            'state' => LiberadoParaProduccionState::getStateId(),
        ]);

        $folio->transitions()->create([
            'user_id' => $this->getUser()->id,
            'prev_state_id' => 6,
            'next_state_id' => FolioAprobadoState::getStateId(),
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $folio->transitions()->create([
            'user_id' => $this->getUser()->id,
            'prev_state_id' => 9,
            'next_state_id' => LiberadoParaProduccionState::getStateId(),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $response = $this->getJson(route('folios.index'));

        $response->assertOk();

        $record = collect($response->json('data'))->firstWhere('id', $folio->id);

        $this->assertNotNull($record);
        $this->assertSame(1, $record['delivery_timing']['target_days']);
        $this->assertSame(2, $record['delivery_timing']['elapsed_days']);
        $this->assertSame('2 dias', $record['delivery_timing']['elapsed_label']);
        $this->assertSame('completed_late', $record['delivery_timing']['status']);
        $this->assertTrue($record['delivery_timing']['is_completed']);
        $this->assertTrue($record['delivery_timing']['is_late']);
    }

    /**
     * @test
     */
    public function test_folio_index_returns_null_delivery_timing_when_approval_transition_is_missing(): void
    {
        $this->asSuperAdmin();

        $folio = Folio::factory()->create([
            'classification' => 'A',
            'state' => FolioAprobadoState::getStateId(),
        ]);

        $response = $this->getJson(route('folios.index'));

        $response->assertOk();

        $record = collect($response->json('data'))->firstWhere('id', $folio->id);

        $this->assertNotNull($record);
        $this->assertNull($record['delivery_timing']);
    }
}
