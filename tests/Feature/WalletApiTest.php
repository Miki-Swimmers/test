<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_increases_balance()
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 100,
            'comment' => 'start'
        ]);
        $response->assertStatus(200)
            ->assertJson(['user_id' => $user->id, 'balance' => 100.0]);
    }

    public function test_withdraw_decreases_balance()
    {
        $user = User::factory()->create();
        $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 150,
        ]);
        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => 50,
        ]);
        $response->assertStatus(200)
            ->assertJson(['user_id' => $user->id, 'balance' => 100.0]);
    }

    public function test_transfer_moves_funds_between_users()
    {
        $from = User::factory()->create();
        $to = User::factory()->create();
        $this->postJson('/api/deposit', [
            'user_id' => $from->id,
            'amount' => 99,
        ]);
        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $from->id,
            'to_user_id' => $to->id,
            'amount' => 12.5,
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'from' => ['user_id' => $from->id, 'balance' => 86.5],
                'to' => ['user_id' => $to->id, 'balance' => 12.5],
            ]);
    }
}


