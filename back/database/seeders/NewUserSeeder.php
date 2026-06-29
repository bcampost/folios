<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NewUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchesId = Branch::pluck('id')->toArray();

        $branches = Branch::get(['id', 'name'])->mapWithKeys(function ($item, $key) {
            return [$item->name => $item->id];
        });

        $users = DB::connection('crm')->table('users')->latest('id')->take(12)->get();

        foreach ($users as $user) {
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
            ]);
        }
    }
}
