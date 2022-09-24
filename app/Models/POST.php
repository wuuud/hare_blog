<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

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
}
