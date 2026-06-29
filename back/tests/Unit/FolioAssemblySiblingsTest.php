<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Folio;
use App\Models\Project;
use Tests\CreatesApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FolioAssemblySiblingsTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->seed();

        $project = Project::factory()->create();

        $folio1 = Folio::factory()->create([
            'description' => 'Folio 1',
            'assembly_number' => 1,
            'project_id' => $project->id
        ]);

        $folio2 = Folio::factory()->create([
            'description' => 'Folio 2',
            'assembly_number' => 1,
            'project_id' => $project->id
        ]);

        $folio3 = Folio::factory()->create([
            'description' => 'Folio 3',
            'assembly_number' => 1,
            'project_id' => $project->id
        ]);

        $assemblySiblingsOfFolio1 = $folio2->assemblySiblings;

        $this->assertCount(2, $assemblySiblingsOfFolio1);
    }
}
