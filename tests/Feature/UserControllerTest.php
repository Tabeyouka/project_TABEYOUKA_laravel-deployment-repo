<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Factories\UserFactory;


class UserControllerTest extends TestCase
{

     use RefreshDatabase;

    public function test_add_user_successfully()
    {
        $user = User::factory()->create();
        $response = $this->postJson("/api/user", $user->toArray());
  
        $response->assertStatus(200)
            ->assertJson(['message' => 'Add user successfully']);
    }

    public function test_show_user_successfully() {
        $user = User::factory()->create();

        $response = $this->getJson("/api/user/{$user->id}");

        $response->assertStatus(200)
            ->assertJson($user->toArray());
    }

    public function test_update_user_successfully() {
        $user = User::factory()->create();

        $response = $this->patchJson("/api/user");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Update user successfully']);
    }
}
