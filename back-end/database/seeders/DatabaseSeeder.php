<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LogicException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('Demo seed data must not be created in production.');
        }

        $this->call([
            AdminSeeder::class,
            Company1Seeder::class,
            Company2Seeder::class,
        ]);
    }
}
