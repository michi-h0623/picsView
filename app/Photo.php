<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class Photo extends Model
{
    protected $perPage = 6;
    protected $keyType = "string";
    protected $appends = [
        "url",
        "likes_count",
        "liked_by_user",
    ];

    protected $visible = [
        "id",
        "owner",
        "url",
        "comments",
        "likes_count",
        "liked_by_user",
    ];

    const ID_LENGTH = 12;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!Arr::get($this->attributes, "id")) {
            $this->setId();
        }
    }

    private function setId()
    {
        $this->attributes["id"] = $this->getRandomId();
    }

    private function getRandomId()
    {
        $characters = array_merge(
            range(0, 9),
            range("a", "z"),
            range("A", "Z"),
            ["-", "_"]
        );

        $length = count($characters);

        $id = "";

        for ($i = 0; $i < self::ID_LENGTH; $i++) {
            $id .= $characters[random_int(0, $length - 1)];
        }

        return $id;
    }

    public function owner()
    {
        return $this->belongsTo("App\User", "user_id", "id", "users");
    }

    public function getUrlAttribute()
    {
        return Storage::cloud()->url($this->attributes["filename"]);
    }

    public function comments()
    {
        return $this->hasMany("App\Comment")->orderBy("id", "desc");
    }

    public function likes()
    {
        return $this->belongsToMany("App\User", "likes")->withTimestamps();
    }

    public function getLikesCountAttribute()
    {
        return $this->likes->count();
    }

    public function getLikedByUserAttribute()
    {
        if (Auth::guest()) {
            return false;
        }

        return $this->likes->contains(function ($user) {
            return $user->id === Auth::user()->id;
        });
    }
}
