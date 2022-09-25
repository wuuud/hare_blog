<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    //DBへの登録を許可する一覧
    protected $fillable = [
        'title',
        'body',
    ];

    /**
     * Get the user that owns the POST
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    //POSTモデルから見ると単数形
    public function user()
    {
        //       UserのhasMany    'foreign_key', 'other_key'(id)を削除
        return $this->belongsTo(User::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    
    public function getImageUrlAttribute()
    {
        //show画面の
        //<img src="{{ Storage::url('images/posts/' . $post->image) }}" alt="" class="mb-4">
        //            $post->imageだがインスタンス化したものを使うので$this
        return Storage::url($this->image_path);
    }


    public function getImagePathAttribute()
    {
        //  上記のリレーションから少し変更。
        return 'images/posts/' . $this->image;
    }
}
