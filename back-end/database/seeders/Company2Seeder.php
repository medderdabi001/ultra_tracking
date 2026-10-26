<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Car;
use App\Models\User;
use App\Models\UserCarPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use LogicException;

class Company2Seeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('Demo seed data must not be created in production.');
        }

        $admin = Admin::where('email', 'admin@admin.com')->firstOrFail();

        $account = Account::firstOrCreate(
            ['name' => 'company2', 'type' => 'company'],
            [
                'status' => 'active',
                'created_by' => $admin->id,
            ],
        );

        $gerant = User::updateOrCreate(
            ['email' => 'gerant2@mail.test'],
            [
                'account_id' => $account->id,
                'name' => 'Gerant Company2',
                'password' => Hash::make('12341234'),
                'role' => 'gerant',
                'status' => 'active',
            ],
        );

        $assistant = User::updateOrCreate(
            ['email' => 'assistant2@mail.test'],
            [
                'account_id' => $account->id,
                'name' => 'Assistant Company2',
                'password' => Hash::make('12341234'),
                'role' => 'assistant',
                'status' => 'active',
            ],
        );

        $car = Car::updateOrCreate(
            ['matricule' => 'DEMO-C2-001'],
            [
                'account_id' => $account->id,
                'marque' => 'Dacia',
                'model' => 'Sandero',
                'color' => 'Blue',
                'fuel_type' => 'gasoline',
                'mileage' => 12000,
                'status' => 'active',
            ],
        );

        UserCarPermission::updateOrCreate(
            [
                'user_id' => $assistant->id,
                'car_id' => $car->id,
            ],
            [
                'can_view' => true,
                'can_view_history' => false,
                'can_cut_engine' => false,
            ],
        );
    }
}
