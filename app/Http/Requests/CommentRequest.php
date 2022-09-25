<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'body' => 'required|string|max:200',
            // formから送られてくる情報にはuserid,postidはない。
            // バリデーションはbodyのみ、
        ];
    }

    public function attributes()
    {
        return [
            // これを書かないと、コメント欄のエラーが
            // 『本文は、200文字以下にしてください。』と本文表示になる
            'body' => 'コメント',
        ];
    }
}
