<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhoto;
use App\Comment;
use App\Http\Requests\StoreComment;
use App\Photo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class PhotoController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth")->except(["index", "download", "show", "like"]);
    }

    public function create(StorePhoto $request)
    {
        // 拡張子を取得
        $extension = $request->photo->extension();

        $photo = new Photo();

        // ファイル名をランダムID値と拡張子で作成
        $photo->filename = $photo->id . "." . $extension;

        // S3にファイルを保存
        Storage::cloud()->putFileAs("", $request->photo, $photo->filename, "public");

        // DB transaction利用開始
        DB::beginTransaction();

        try {
            Auth::user()->photos()->save($photo);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Storage::cloud()->delete($photo->filename);
            throw $exception;
        }

        return response($photo, 201);
    }

    public function delete(string $id)
    {
        $photo = Photo::where("id", $id)->with("likes")->first();

        // DBへの反映
        DB::beginTransaction();

        try {

            Auth::user()->photos()->where("id", $id)->delete();
            DB::commit();

            // ファイル削除
            Storage::cloud()->delete($photo->filename);
            
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $photo;
    }

    public function index()
    {
        $photos = Photo::with(["owner", "likes"])
            ->orderBy(Photo::CREATED_AT, "desc")->paginate();

        return $photos;
    }

    public function download(Photo $photo)
    {
        if (!Storage::cloud()->exists($photo->filename)) {
            abort(404);
        }

        $headers = [
            "Content-Type" => "application/octet-stream",
            "Content-Disposition" => '"attachment; filename="' . $photo->filename . '"',
        ];

        return response(Storage::cloud()->get($photo->filename), 200, $headers);
    }

    public function show(string $id)
    {
        $photo = Photo::where("id", $id)->with(["owner", "comments.author", "likes"])->first();

        return $photo ?? abort(404);
    }

    public function addComment(Photo $photo, StoreComment $request)
    {
        $comment = new Comment();
        $comment->content = $request->get("content");
        $comment->user_id = Auth::user()->id;
        $photo->comments()->save($comment);

        $new_comment = Comment::where("id", $comment->id)->with("author")->first();

        return response($new_comment, 201);
    }

    public function like(string $id)
    {
        $photo = Photo::where("id", $id)->with("likes")->first();

        if (!$photo) {
            abort(404);
        }

        $photo->likes()->detach(Auth::user()->id);
        $photo->likes()->attach(Auth::user()->id);

        return ["photo_id" => $id];
    }

    public function unlike(string $id)
    {
        $photo = Photo::where("id", $id)->with("likes")->first();

        if (!$photo) {
            abort(404);
        }

        $photo->likes()->detach(Auth::user()->id);

        return ["photo_id" => $id];
    }
}
