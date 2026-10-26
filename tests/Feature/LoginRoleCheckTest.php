<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginRoleCheckTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $farmer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::query()->create([
            'full_name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '0901111111',
            'password_hash' => Hash::make('password123'),
            'address' => 'Customer Street',
            'role' => 'CUSTOMER',
            'email_verified_at' => now(),
        ]);

        $this->farmer = User::query()->create([
            'full_name' => 'Test Farmer',
            'email' => 'farmer@example.com',
            'phone' => '0902222222',
            'password_hash' => Hash::make('password123'),
            'address' => 'Farm Street',
            'role' => 'FARMER',
            'email_verified_at' => now(),
        ]);
    }

    public function test_customer_can_login_to_customer_portal(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'customer@example.com',
            'password' => 'password123',
            'role' => 'CUSTOMER',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'CUSTOMER');
    }

    public function test_farmer_cannot_login_to_customer_portal(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'farmer@example.com',
            'password' => 'password123',
            'role' => 'CUSTOMER',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'This account is a farmer account. Please log in as a farmer.');
    }

    public function test_farmer_can_login_to_farmer_portal(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'farmer@example.com',
            'password' => 'password123',
            'role' => 'FARMER',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'FARMER');
    }

    public function test_customer_cannot_login_to_farmer_portal(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'customer@example.com',
            'password' => 'password123',
            'role' => 'FARMER',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'This account is not a farmer account. Please log in as a customer.');
    }

    public function test_login_without_role_still_works(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'customer@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);
    }
}
