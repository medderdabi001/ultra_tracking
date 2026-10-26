<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Car;
use Illuminate\Database\Seeder;
use LogicException;

class Company1CarsSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('Demo seed data must not be created in production.');
        }

        $account = Account::where('name', 'company1')
            ->where('type', 'company')
            ->firstOrFail();

        $cars = [
            [
                'matricule' => 'DEMO-C1-001',
                'marque' => 'Dacia',
                'model' => 'Logan',
                'color' => 'White',
                'fuel_type' => 'diesel',
                'mileage' => 45200,
            ],
            [
                'matricule' => 'DEMO-C1-002',
                'marque' => 'Renault',
                'model' => 'Clio',
                'color' => 'Gray',
                'fuel_type' => 'gasoline',
                'mileage' => 28750,
            ],
            [
                'matricule' => 'DEMO-C1-003',
                'marque' => 'Toyota',
                'model' => 'Corolla',
                'color' => 'Black',
                'fuel_type' => 'hybrid',
                'mileage' => 16300,
            ],
        ];

        foreach ($cars as $car) {
            Car::updateOrCreate(
                ['matricule' => $car['matricule']],
                [
                    ...$car,
                    'account_id' => $account->id,
                    'status' => 'active',
                ],
            );
        }
    }
}
