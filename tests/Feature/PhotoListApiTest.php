<?php

namespace Tests\Feature;

use App\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoListApiTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */

    public function should_正しい構造のJSONを返却する()
    {
        factory(Photo::class, 5)->create();

        $response = $this->json("GET", route("photo.index"));

        $photos = Photo::with(["owner"])->orderBy("created_at", "desc")->get();

        $expected_data = $photos->map(function ($photo) {
            return [
                "id" => $photo->id,
                "likes_count" => 0,
                "liked_by_user" => false,
                "url" => $photo->url,
                "owner" => [
                    "name" => $photo->owner->name,
                ],
            ];
        })
            ->all();

        $response->assertStatus(200)
            ->assertJsonCount(5, "data")
            ->assertJsonFragment(
                [
                    "data" => $expected_data,
                    "likes_count" => 0,
                    "liked_by_user" => false,
                ]
            );
    }
}