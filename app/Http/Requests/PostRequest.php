<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
        //storeかupdateか確認。
        //現在のroute(sail artisan route:listの出力結果のrouteの列の値)を取得

        // 
        $route = $this->route()->getName();
        // 1.storeとupdateの両方
        $rule = [
            'title' => 'required|string|max:50',
            'body' => 'required|string|max:2000',
            //imageを取り込みため、形式を設定
            //imageでファイルが画像（jpg、jpeg、png、bmp、gif、svg、webp）かチェックを行う。
            //mimes:でファイルの拡張子のチェック
            //editのために修正
            //title、body、imageのバリデーションを設定していますが、編集時は画像が必須ではない。
            // 'image' => 'required|file|image|mimes:jpg,png',
        ];
        // 2.storeとupdateのどちらか
        if ($route === 'posts.store' ||
        //update かつファイルが渡されたときに、ファイルを確認する
        //thisはPostRequestの内容。詳しく言うと、もとはFormRequest、Requestから継承PostRequestの内容。
            ($route === 'posts.update' && $this->file('image'))) {
            $rule['image'] = 'required|file|image|mimes:jpg,png';
        }

        return $rule;
    }
}
