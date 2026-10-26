<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    public function test_unauthenticated_admin_api_requests_return_json_instead_of_redirecting(): void
    {
        $response = $this->post('/api/admin/accounts', []);

        $response
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }
}
