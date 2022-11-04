<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\IdentityProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class OAuthController extends Controller
{
    //
    public function redirectToProvider()
    {
        return Socialite::driver('github')->redirect();
    }

    public function oauthCallback()
    {
        // 認証情報が返ってこなかった場合はログイン画面にリダイレクト
        try {
            $socialUser = Socialite::with('github')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['oauth_error' => '予期せぬエラーが発生しました']);
        }
        // 認証情報を表示
        // dd($socialUser);

        // firstOrNew
        // emailで検索してユーザーが見つかればそのユーザーを、見つからなければ新しいインスタンスを生成
        $user = User::firstOrNew(['email' => $socialUser->getEmail()]);

        // ユーザーが認証済みか確認
        // 今とってきたユーザーはあるか
        if (!$user->exists) {
        // インスタンス化
                                //    Nicknameあれば     なければ普通のname
            $user->name = $socialUser->getNickname() ?? $socialUser->name;
            $identityProvider = new IdentityProvider([
            // 一意のもの
                'uid' => $socialUser->getId(),
                'provider' => 'github'
            ]);
            // 2.保存
            DB::beginTransaction();
            try {
                $user->save();
                // UserIDも入った＄identityProviderが必要
                $user->identityProvider()->save($identityProvider);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()
                    ->route('login')
                    ->withErrors(['transaction_error' => '保存に失敗しました']);
            }
        }
        // ログイン
        Auth::login($user);
        // app/http/providers/RouteServiceProvider
        // class RouteServiceProvider extends ServiceProvider
        // public const HOME = '/';
        return redirect(RouteServiceProvider::HOME);
    }
}
