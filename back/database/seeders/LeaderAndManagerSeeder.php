<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LeaderAndManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managers = [
            [
                'name' => 'Ulises',
                'id'   => 24,
                'branches' => [1,2]
            ],
            [
                'name' => 'Omar',
                'id'   => 26,
                'branches' => [4]
            ],
            [
                'name' => 'Cesar Reyes',
                'id'   => 27,
                'branches' => [3]
            ],
        ];

        foreach ($managers as $manager) {
            $managerModel = User::find($manager['id']);

            $managerModel->branches()->sync($manager['branches']);
        }

        $leaders = [
            [
                'name' => 'Jassiel',
                'users' => [
                    'Mayra',
                    'Kahtia',
                    'Mario Alejandro',
                    'Dignangel',
                    'Susana',
                    'Mireya',
                    'Enrique Emmanuel',
                    'Mauricio Ávila'
                ]
            ],
            [
                'name' => 'Rodrigo Bustillo',
                'users' => [
                    'Tania Gallegos',
                    'Liz Cervantes'
                ]
            ],
            [
                'name' => 'Fabiola Partida',
                'users' => [
                    'Karla Adriana',
                    'Esteban Sánchez'
                ],
            ],
            [
                'name' => 'Carolina Reyes',
                'users' => [
                    'Sharon Garcia',
                    'José Ignacio',
                    'Elizabeth Duran',
                    'Diego Andrés'
                ],
            ],
        ];

        foreach ($leaders as $leader) {
            $leaderModel = User::where('name', 'like', '%'.$leader['name'].'%')->first();

            $userQuery = User::query()
                ->select('id', 'name');
            foreach ($leader['users'] as $key => $user) {
                if ($key === 0) {
                    $userQuery->where('name', 'like', '%'.$user.'%');
                } else {
                    $userQuery->orWhere('name', 'like', '%'.$user.'%');
                }
            }

            // dd($userQuery->get()->toArray());

            $leaderModel->assignedUsers()->sync($userQuery->get()->pluck('id'));
        }
    }
}
