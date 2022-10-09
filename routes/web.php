<?php

use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Auth\OAuthController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//ログイン後はダッシュボード画面app/Providers/RouteServiceProvider.phpも修正
Route::get('/', [PostController::class, 'index'])
    ->name('root');


//breezeインストールしたため。require __DIR__.'/auth.php';
// routesにも auth.phpファイルが追加される
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// 1.認証必要な動作
Route::resource('posts', PostController::class)
//resourceの中で、認証が必要なものだけを下記に残すindex,showを外す
    ->only(['create', 'store', 'edit', 'update', 'destroy'])
// authで認証しているときだけのroute.登録、更新、削除を実行できないように制御
//middlewareはHTTPリクエストが送られたタイミングで実行される処理
    ->middleware('auth');

// 2.認証不要な動作
Route::resource('posts', PostController::class)
    ->only(['show', 'index']);

//3.コメント
//コメントは記事に紐付いているため(特定の記事に対してのコメントになるため)、Route::resource('posts.comments')
Route::resource('posts.comments', CommentController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy'])
    ->middleware('auth');

require __DIR__.'/auth.php';

// authから始まるルーティングに認証前にアクセスがあった場合　groupにしている
// 頭にauthがつくか
Route::prefix('auth')->middleware('guest')->group(function () {
    // auth/githubにアクセスがあった場合はOAuthControllerのredirectToProviderアクションへルーティング
    // ユーザーをOAuthプロバイダにリダイレクトするためのもの
    Route::get('/github', [OAuthController::class, 'redirectToProvider'])
        ->name('redirectToProvider');

    // auth/github/callbackにアクセスがあった場合はOAuthControllerのoauthCallbackアクションへルーティング
    // 認証後にプロバイダからのコールバックを受信
    Route::get('/github/callback', [OAuthController::class, 'oauthCallback'])
        ->name('oauthCallback');
});
