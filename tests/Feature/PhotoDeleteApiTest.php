<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\UploadedFile;
use App\User;
use App\Photo;
use App\Comment;

class PhotoDeleteApiTest extends TestCase
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
    public function should_ファイルを削除する()
    {
        // Photoを作成
        factory(Photo::class)->create()->each(function ($photo) {
            $photo->comments()->saveMany(factory(Comment::class, 3)->make());
        });
        $photo = Photo::first();
        // dd($photo);

        // ファイルアップロード
        Storage::fake("s3");

        $uploadResponse = $this->actingAs($this->user)
            ->json("POST", route("photo.create"), [
                // ダミーファイルを作成して送信
                "photo" => UploadedFile::fake()->image("photo.jpg"),
            ]);

        // アップロードされているか？
        $uploadResponse->assertStatus(201);

        // dump用
        // dump("status: " . $uploadResponse->status());

        // uploadResponseをデコードする
        $decodedResponse = json_decode($uploadResponse->content());

        // dump用
        // dd("content: " . var_dump($decodedResponse));

        // アップロードされているファイル名とuploadResponseのファイル名を比較して同一のものがあることを確認
        // Storage::cloud()->assertExists(basename($decodedResponse->url));
        Storage::cloud()->assertExists(basename($decodedResponse->url));

        // ファイル削除
        $response = $this->actingAs($this->user)
            ->json("DELETE", route("photo.delete", [
                "id" => $photo->id,
            ]));

        $response->assertStatus(200);

        // 今のところcloudのファイルが削除されているかの確認をassertMissingで行うことができていない
        // アップロードされているファイル名とuploadResponseのファイル名を比較して同一のものがあることを確認
        // Storage::cloud()->assertMissing(basename($decodedResponse->url));

        // DBへの反映
    }

    /**
     * @test
     */
    public function should_DBエラー時はファイルを削除しない()
    {
        Storage::fake("s3");

        // Photoを作成
        factory(Photo::class)->create()->each(function ($photo) {
            $photo->comments()->saveMany(factory(Comment::class, 3)->make());
        });

        $photo = Photo::first();
        $id = $photo->id;

        // アップロード
        $uploadResponse = $this->actingAs($this->user)
            ->json("POST", route("photo.create"), [
                // ダミーファイルを作成して送信
                "photo" => UploadedFile::fake()->image("photo.jpg"),
            ]);

        Schema::drop("photos");

        // ファイル削除
        $response = $this->actingAs($this->user)
            ->json("DELETE", route("photo.delete", [
                "id" => $id,
            ]));

        $response->assertStatus(500);
        $this->assertEquals(1, count(Storage::cloud()->files()));
    }

    /**
     * @test
     */
    public function should_ファイル削除エラー時はDB削除をしない()
    {

        Storage::fake("s3");

        // Photoを作成
        factory(Photo::class)->create()->each(function ($photo) {
            $photo->comments()->saveMany(factory(Comment::class, 3)->make());
        });

        $photo = Photo::first();
        $id = $photo->id;

        // アップロード
        $uploadResponse = $this->actingAs($this->user)
            ->json("POST", route("photo.create"), [
                // ダミーファイルを作成して送信
                "photo" => UploadedFile::fake()->image("photo.jpg"),
            ]);

        Storage::shouldReceive("cloud")
            ->once()
            ->andReturnNull();

        // ファイル削除
        $response = $this->actingAs($this->user)
            ->json("DELETE", route("photo.delete", [
                "id" => $id,
            ]));

        $response->assertStatus(500);
        $this->assertNotEmpty(Photo::all());
    }
}
