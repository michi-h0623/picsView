<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// ------------------------------------------------------------------------

// ユーザ登録
Route::post("/register", "Auth\RegisterController@register")->name("register");
// ログイン
Route::post("/login", "Auth\LoginController@login")->name("login");
// ログアウト
Route::post("/logout", "Auth\LoginController@logout")->name("logout");
// ユーザ取得
Route::get("/user", function () {
    return Auth::user();
})->name("user");
// token regenerate
Route::get("/refresh-token", function (Request $request) {
    $request->session()->regenerateToken();

    return response()->json();
});

// ------------------------------------------------------------------------

// ファイルアップロード
Route::post("/photos", "PhotoController@create")->name("photo.create");
// コメント投稿
Route::post("/photos/{photo}/comments", "PhotoController@addComment")->name("photo.comment");
// photo一覧取得
Route::get("/photos", "PhotoController@index")->name("photo.index");
// photo詳細取得
Route::get("/photos/{id}", "PhotoController@show")->name("photo.show");
// いいねをつける
Route::put("/photos/{id}/like", "PhotoController@like")->name("photo.like");
// いいねを外す
Route::delete("/photos/{id}/like", "PhotoController@unlike");
// ファイル削除
Route::delete("/photos/{id}", "PhotoController@delete")->name("photo.delete");
