<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Car;
use App\Models\User;
use App\Models\UserCarPermission;
use Database\Seeders\AdminSeeder;
use Database\Seeders\Company2Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Company2SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_company2_users_car_and_assistant_permissions_idempotently(): void
    {
        $this->seed(AdminSeeder::class);

        $this->seed(Company2Seeder::class);
        $this->seed(Company2Seeder::class);

        $account = Account::where('name', 'company2')->firstOrFail();
        $admin = Admin::where('email', 'admin@admin.com')->firstOrFail();
        $car = Car::where('matricule', 'DEMO-C2-001')->firstOrFail();
        $assistant = User::where('email', 'assistant2@mail.test')->firstOrFail();

        $this->assertSame('company', $account->type);
        $this->assertSame($admin->id, $account->created_by);
        $this->assertSame(2, User::where('account_id', $account->id)->count());
        $this->assertSame(1, Car::where('account_id', $account->id)->count());
        $this->assertSame('gerant', User::where('email', 'gerant2@mail.test')->value('role'));
        $this->assertSame($account->id, $car->account_id);

        $this->assertDatabaseHas('user_car_permissions', [
            'user_id' => $assistant->id,
            'car_id' => $car->id,
            'can_view' => true,
            'can_view_history' => false,
            'can_cut_engine' => false,
        ]);
        $this->assertSame(1, UserCarPermission::where('user_id', $assistant->id)->count());
    }
}
