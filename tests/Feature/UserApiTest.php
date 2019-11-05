<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;

class UserApiTest extends TestCase
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
    public function should_ログイン中のユーザを返却する()
    {
        $response = $this->actingAs($this->user)->json("GET", route("user"));

        $response
            ->assertStatus(200)
            ->assertJson([
                "name" => $this->user->name,
            ]);
    }
    public function should_ログインされていない場合は空文字を返却する()
    {
        $response = $this->json("GET", route("user"));

        $response->assertStatus(200);
        $this->assertEquals("", $response->content());
    }
}
