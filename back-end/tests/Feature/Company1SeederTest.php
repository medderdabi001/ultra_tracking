<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Car;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\Company1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Company1SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_company1_with_a_manager_and_an_assistant_idempotently(): void
    {
        $this->seed([
            AdminSeeder::class,
            Company1Seeder::class,
        ]);

        $this->seed(Company1Seeder::class);

        $account = Account::where('name', 'company1')->firstOrFail();
        $admin = Admin::where('email', 'admin@admin.com')->firstOrFail();

        $this->assertSame('company', $account->type);
        $this->assertSame($admin->id, $account->created_by);
        $this->assertSame(2, User::where('account_id', $account->id)->count());
        $this->assertDatabaseHas('users', [
            'email' => 'gerant@mail.test',
            'role' => 'gerant',
            'account_id' => $account->id,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'assistant@mail.test',
            'role' => 'assistant',
            'account_id' => $account->id,
        ]);
        $this->assertSame(3, Car::where('account_id', $account->id)->count());
        $this->assertDatabaseHas('cars', [
            'account_id' => $account->id,
            'matricule' => 'DEMO-C1-001',
            'marque' => 'Dacia',
            'model' => 'Logan',
        ]);
    }
}
