<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\Company2Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssistantCarVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_company2_assistant_only_sees_cars_with_view_permission(): void
    {
        $this->seed([
            AdminSeeder::class,
            Company2Seeder::class,
        ]);

        $assistant = User::where('email', 'assistant2@mail.test')->firstOrFail();
        Sanctum::actingAs($assistant);

        $this->getJson('/api/client/vehicles/live')
            ->assertOk()
            ->assertJsonCount(1, 'cars')
            ->assertJsonPath('cars.0.matricule', 'DEMO-C2-001')
            ->assertJsonPath('cars.0.telemetry', null);
    }
}
