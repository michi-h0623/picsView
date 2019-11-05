<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /**
     * @test
     */

    public function should_登録済みのユーザを認証して更新する()
    {

        $this->withoutExceptionHandling();

        $response = $this->json("POST", route("login"), [
            "email" => $this->user->email,
            "password" => 'password',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(["name" => $this->user->name]);

        $this->assertAuthenticatedAs($this->user);
    }
}
