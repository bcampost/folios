<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $branchesId = Branch::pluck('id')->toArray();

        // User::factory(10)->create([
        //     'branch_id' => fn() => fake()->randomElement($branchesId)
        // ]);

        // Get branches and map name to key and value to the id
        $branches = Branch::get(['id', 'name'])->mapWithKeys(function ($item, $key) {
            return [$item->name => $item->id];
        });

        $newUsers = collect([
            [
                "Nombre" => "Andrea Altamirano Morales",
                "Usuario" => "aaltamirano",
                "Contraseña" => "It4li4.99"
            ],
            [
                "Nombre" => "Gerardo Enrique Flores Rivera",
                "Usuario" => "gflores",
                "Contraseña" => "It4li4.99"
            ],
            [
                "Nombre" => "Jose Gregori Cedillo",
                "Usuario" => "jgregori",
                "Contraseña" => "It4li4.99"
            ],
            [
                "Nombre" => "Karla Andrea Ortiz Rocha",
                "Usuario" => "kortiz",
                "Contraseña" => "It4li4.99"
            ],
            [
                "Nombre" => "Ariadna Pilvoras Cortés",
                "Usuario" => "apilvoras",
                "Contraseña" => "It4li4.99"
            ],
            [
                "Nombre" => "Margarito Guevara Tecuanhuehue",
                "Usuario" => "mguevarat",
                "Contraseña" => "It4li4.99"
            ],
            [
                "Nombre" => "Joan Antonio López Romero",
                "Usuario" => "jlopez",
                "Contraseña" => "It4li4.99"
            ],
            [
                "Nombre" => "Miriam Garces Ugalde",
                "Usuario" => "mgarces",
                "Contraseña" => "It4li4.99"
            ],
            [
                "Nombre" => "Claudia Isela Hernandez Rodriguez",
                "Usuario" => "chernandez",
                "Contraseña" => "It4li4.99"
            ],
            [
                "Nombre" => "Kelly Daniela Falcón Rivera",
                "Usuario" => "kfalcon",
                "Contraseña" => "It4li4.99"
            ]
        ]);

        $newUsersUsuarios = $newUsers->pluck('Usuario')->toArray();

        $globalUsers = DB::connection('users')
            ->table('users')
            ->select('id', 'name', 'usuario', 'email', 'branch')
            ->whereIn('usuario', $newUsersUsuarios)
            ->get();

        $missingUsers = $newUsers->filter(function ($user) use ($globalUsers) {
            return !$globalUsers->where('usuario', $user['Usuario'])->first();
        });

        $crmUsers = DB::connection('crm')
            ->table('users')
            ->select('id', 'name', 'email', 'branch', 'role', 'main_user_id')
            ->whereIn('email', $globalUsers->pluck('email')->toArray())
            ->get();

        // dd($missingUsers->toArray());

        // dd($crmUsers->toArray());
        // dd($globalUsers->toArray());

        foreach ($crmUsers as $user) {
            User::updateOrCreate([
                'crm_id' => $user->id
            ], [
                'crm_id' => $user->id,
                'branch' => $user->branch,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'branch_id' => $branches[$user->branch] ?? null,
                // 'password' => $user->password,
                'password' => Hash::make('It4li4.01'),
                'main_user_id' => $user->main_user_id
            ]);
        }


        // $users = DB::connection('crm')->table('users')->get();

        // foreach ($users as $user) {
        //     User::updateOrCreate([
        //         'id' => $user->id
        //     ], [
        //         'crm_id' => $user->id,
        //         'branch' => $user->branch,
        //         'name' => $user->name,
        //         'email' => $user->email,
        //         'role' => $user->role,
        //         'branch_id' => $branches[$user->branch] ?? null,
        //         // 'password' => $user->password,
        //         'password' => Hash::make('It4li4.01'),
        //     ]);
        // }

        // $leaders = DB::connection('crm')->table('users')->where('role', 'team_leader')->pluck('id')->toArray();
        // $managerUser = DB::connection('crm')->table('manager_user')
        //     ->rightJoin('users as u', 'manager_user.user_id', '=', 'u.id')
        //     ->leftJoin('users as o', 'manager_user.owner_id', '=', 'o.id')
        //     ->whereIn('owner_id', $leaders)
        //     ->get()
        //     ->toArray();

        // foreach ($managerUser as $user) {
        //     DB::table('manager_user')->insert([
        //         'user_id' => $user->user_id,
        //         'owner_id' => $user->owner_id
        //     ]);
        // }

        // User::factory()->create([
        //     'name' => 'Superadmin',
        //     'email' => 'superadmin2@test.com',
        //     'role' => RoleEnum::Superadmin,
        //     'branch_id' => fake()->randomElement($branchesId)
        // ]);

        // User::factory()->create([
        //     'name' => 'Octavio Miranda',
        //     'email' => 'omiranda@crisa.com.mx',
        //     'role' => RoleEnum::Engineering,
        //     'branch_id' => fake()->randomElement($branchesId)
        // ]);

        // User::factory()->create([
        //     'name' => 'Brenda Yáñez',
        //     'email' => 'byanez@lineaitalia.com.mx',
        //     'role' => RoleEnum::Engineering,
        //     'branch_id' => fake()->randomElement($branchesId)
        // ]);

        // User::factory()->create([
        //     'name' => 'Ingeniería',
        //     'email' => 'ingenieria@test.com',
        //     'role' => RoleEnum::Engineering,
        //     'branch_id' => fake()->randomElement($branchesId)
        // ]);

        // User::factory()->create([
        //     'name' => 'Finanzas',
        //     'email' => 'finanzas@test.com',
        //     'role' => RoleEnum::Finance,
        //     'branch_id' => fake()->randomElement($branchesId)
        // ]);

        // $davidFinance = User::firstWhere('email', 'dlopezr@lineaitalia.com.mx');

        // if($davidFinance) {
        //     $davidFinance->update([
        //         'role' => RoleEnum::Finance
        //     ]);
        // }

    }
}
