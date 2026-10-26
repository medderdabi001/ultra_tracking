<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use LogicException;

class Company1Seeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('Demo seed data must not be created in production.');
        }

        $admin = Admin::where('email', 'admin@admin.com')->firstOrFail();

        $account = Account::firstOrCreate(
            ['name' => 'company1', 'type' => 'company'],
            [
                'status' => 'active',
                'created_by' => $admin->id,
            ],
        );

        User::updateOrCreate(
            ['email' => 'gerant@mail.test'],
            [
                'account_id' => $account->id,
                'name' => 'Gerant Company1',
                'password' => Hash::make('12341234'),
                'role' => 'gerant',
                'status' => 'active',
            ],
        );

        User::updateOrCreate(
            ['email' => 'assistant@mail.test'],
            [
                'account_id' => $account->id,
                'name' => 'Assistant Company1',
                'password' => Hash::make('12341234'),
                'role' => 'assistant',
                'status' => 'active',
            ],
        );

        $this->call(Company1CarsSeeder::class);
    }
}
