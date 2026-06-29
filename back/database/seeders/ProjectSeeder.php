<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\User;
use App\Models\Folio;
use App\Models\Product;
use App\Models\Project;
use App\Models\Customer;
use App\Enums\FolioTypeEnum;
use Illuminate\Database\Seeder;
use App\States\Folio\CostoAsignadoState;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\FolioSolicitadoState;
use App\States\Folio\PrevioSolicitadoState;
use App\States\Folio\PrecioDeListaAsignadoState;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProjectSeeder extends Seeder
{

    public function run(): void
    {
        $dealIds = Deal::latest()->limit(100)->pluck('id');
        $productIds = Product::inRandomOrder()->limit(100)->pluck('id');
        $userIds = User::latest()->pluck('id');

        $projects = Project::factory(100)
            ->create([
                'discount' => 0,
                'payment_term_id' => 5,
                'payment_by_customer_platform' => true,
                'deal_id' => fn() => $dealIds->random(),
                'owner_id' => fn() => $userIds->random(),
                'created_at' => fn() => fake()->dateTimeBetween('-3 weeks', '-2 days'),
            ])
            ->each(function (Project $project) use ($productIds) {
                $folio = Folio::factory()
                    ->create([
                        'project_id' => $project->id,
                        'created_at' => $project->created_at,
                        'reference_product' => fn() => $productIds->random(),
                    ]);
            });

        $folios = Folio::query()
            ->oldest()
            ->limit(60)
            ->get();


        foreach ($folios as $folio) {
            $folio->state = PrevioAprobadoState::getStateId();
            $folio->save();

            $folio->transitions()->create([
                'prev_state_id' => PrevioSolicitadoState::getStateId(),
                'next_state_id' => PrevioAprobadoState::getStateId(),
                'user_id' => $userIds->random(),
                'created_at' => fake()->dateTimeBetween($folio->created_at, 'now'),
            ]);
        }

        $folios = Folio::query()
            ->where('state', PrevioAprobadoState::getStateId())
            ->oldest()
            ->limit(30)
            ->get();

        foreach ($folios as $folio) {
            $cost = fake()->numberBetween(1_000, 5_000);
            $folio->state = CostoAsignadoState::getStateId();
            $folio->cost = $cost;
            // $folio->list_price = $cost * rand(2, 3);
            $folio->save();

            $latestTransaction = $folio->transitions()->latest()->first();

            $folio->transitions()->create([
                'prev_state_id' => PrevioAprobadoState::getStateId(),
                'next_state_id' => CostoAsignadoState::getStateId(),
                'user_id' => $userIds->random(),
                'created_at' => fake()->dateTimeBetween($latestTransaction->created_at, 'now'),
            ]);
        }

        $folios = Folio::query()
            ->where('state', CostoAsignadoState::getStateId())
            ->oldest()
            ->limit(15)
            ->get();

        // foreach ($folios as $folio) {
        //     $folio->state = FolioSolicitadoState::getStateId();
        //     $folio->type = FolioTypeEnum::Folio;
        //     $folio->list_price = $folio->cost * rand(1.8, 2.5);
        //     $folio->save();

        //     $latestTransaction = $folio->transitions()->latest()->first();

        //     $folio->transitions()->create([
        //         'prev_state_id' => CostoAsignadoState::getStateId(),
        //         'next_state_id' => FolioSolicitadoState::getStateId(),
        //         'user_id' => $userIds->random(),
        //         'created_at' => fake()->dateTimeBetween($latestTransaction->created_at, 'now'),
        //     ]);
        // }

        $folios = Folio::query()
            ->where('state', CostoAsignadoState::getStateId())
            ->oldest()
            ->limit(15)
            ->get();

        foreach ($folios as $folio) {
            $folio->state = PrecioDeListaAsignadoState::getStateId();
            $folio->list_price = $folio->cost * rand(1.8, 2.5);
            $folio->save();

            $latestTransaction = $folio->transitions()->latest()->first();

            $folio->transitions()->create([
                'prev_state_id' => CostoAsignadoState::getStateId(),
                'next_state_id' => PrecioDeListaAsignadoState::getStateId(),
                'user_id' => $userIds->random(),
                'created_at' => fake()->dateTimeBetween($latestTransaction->created_at, 'now'),
            ]);
        }

        $folios = Folio::query()
            ->where('state', PrecioDeListaAsignadoState::getStateId())
            ->oldest()
            ->limit(10)
            ->get();

        foreach ($folios as $folio) {
            $folio->state = FolioSolicitadoState::getStateId();
            $folio->type = FolioTypeEnum::Folio;
            $folio->list_price = $folio->cost * rand(1.8, 2.5);
            $folio->save();

            $latestTransaction = $folio->transitions()->latest()->first();

            $folio->transitions()->create([
                'prev_state_id' => PrecioDeListaAsignadoState::getStateId(),
                'next_state_id' => FolioSolicitadoState::getStateId(),
                'user_id' => $userIds->random(),
                'created_at' => fake()->dateTimeBetween($latestTransaction->created_at, 'now'),
            ]);
        }

    }
}
