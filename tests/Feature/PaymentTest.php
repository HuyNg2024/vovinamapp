<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_endpoint_returns_vnp_url_for_valid_amount()
    {
        // 1. Arrange: Create user and authenticate
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // 2. Act: Send valid payment request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pay', [
            'amount' => 500000,
            'orderInfo' => 'Thanh toan don hang test',
        ]);

        // 3. Assert: VNPay URL generated successfully
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'vnpUrl'
        ]);
        
        // Assert the URL actually points to VNPay
        $this->assertStringContainsString('sandbox.vnpayment.vn', $response->json('vnpUrl'));
    }

    public function test_payment_endpoint_rejects_negative_amount()
    {
        // 1. Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // 2. Act: Send malicious negative amount
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pay', [
            'amount' => -500000,
            'orderInfo' => 'Hacker test',
        ]);

        // 3. Assert: Should fail validation (422 Unprocessable Entity)
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_payment_endpoint_requires_authentication()
    {
        // 1. Act: Attempt to pay without token
        $response = $this->postJson('/api/pay', [
            'amount' => 500000,
            'orderInfo' => 'No auth test',
        ]);

        // 2. Assert: Unauthorized
        $response->assertStatus(401);
    }
}
