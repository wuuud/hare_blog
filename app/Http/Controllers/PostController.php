<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::all();
        // dd($posts);
        // created_atの降順でデータを取得     latest 
        // simplePaginate 前後 1ページに表示する件数を制限し、ページ下部にページネーション
        // pagename       1/2  1ページに表示する件数を制限し、ページ下部にページネーション
        // model-post.php-publicにおける
        // PostモデルPost::with('userというリレイションを使って') 
        // function user(←リレイション名) return $this->belongsTo(User::class); でリレイション名を作った
        //       UserのhasMany    'foreign_key', 'other_key'(id)を削除
        $posts = Post::with('user')->latest()->paginate(4);
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        //fillableをpost.phpに設定。
        //fillable〜〜でなく、
        //$post = new Post($request->all());で
        // Postモデルの実態化。その中身入り。$request->all()がfillableの意味。
        // create postsの画面http://localhost/posts/createで画像以外 新規の場合はid来てない。フォームにナシ
        $post = new Post($request->all());
        //              csrfで認証済み→認証したユーザー  そのID
        //  画像以外              user() ユーザーの情報を取ってくるメソッド名 決まり
        $post->user_id = $request->user()->id;
        //https://laraweb.net/tutorial/2707/ 
        // アップロードしたファイルの取得
        // create.bladeの <input type="file" name="image"
        // fileはtype属性。imageはname属性。 
        // 画像を取得   $file input typeのfile = $request->file(input nameの'image');
        $file = $request->file('image');
        // ファイル名を保存  取得したときの年月日時分秒      ファイル名を取得
        //取得したときの年月日時分秒で同じファイル名が投稿されても別に保存できるように
        //$post->image = date('YmdHis') . '_' . $file->getClientOriginalName();
        //一番下のアクションにprivate static function createFileNameを追加したことで、
        //ファイル名だけ作って中身なし。中身は仮置においてある。  ファイル名作成方法を変更するときに1箇所アクションを変えるだけでOK
        $post->image = self::createFileName($file);
        // トランザクション開始
        DB::beginTransaction();
        try {
            // タイトル。本文、写真のファイル名を保存
            $post->save();

            // 写真本体保存に失敗した時   putFileファイルを送るAs名前を贈る 
            if (!Storage::putFileAs('images/posts', $file, $post->image)) {
                // 例外を投げてロールバックさせる
                //newある！インスタンス化だ！！＄\Exception = new \Exception;
                throw new \Exception('画像ファイルの保存に失敗しました。');
            }
            // 写真本体に保存 トランザクション終了(成功)
            DB::commit();

            //$e = Exception
        } catch (\Exception $e) {
            // トランザクション終了(失敗)
            DB::rollback();
            // 元の画面にとどまる 元のデータを保存  コメント.＄eはtry文に記載済
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()
            ->route('posts.show', $post)
            //withでメッセージを出す with(メッセージのキー, メッセージのvalue)
            //show.bladeにif文を書く。sail npm run devを実行するとCSSが対応。
            ->with('notice', '記事を登録しました');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $post = Post::find($id);
        // ユーザー情報も取得できる 
        $post = Post::with('user')->find($id);
        // データを取得してくる
        // withはデータを取った後は使えないので 
        // load(['user'])で後からユーザー情報を取得 Eager loading(N＋1問題)の解決のために必要。
        $comments = $post->comments()->latest()->get()->load(['user']);
        // return view('posts.show', compact('post'));
        // 下記の書き方でユーザー情報も取得できる
        return view('posts.show', compact('post', 'comments'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\PostRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PostRequest $request, $id)
    {
        //対象データを1つとる.
        //新規データと似ているが、少しややこしい。
        $post = Post::find($id);
        //   投稿したいユーザー    このユーザーがupdateできるか、この投稿について
        // cannotなので更新権限がなければ結果がtrue
        if ($request->user()->cannot('update', $post)) {
            //更新できなかった場合。この操作は難しい。
            return redirect()->route('posts.show', $post)
                ->withErrors('自分の記事以外は更新できません');
        }
        // updateされているか
        $file = $request->file('image');
        // ふぁいるがあるかどうか、あれば処理
        if ($file) {
            // $delete_file_path = 'images/posts/' . $post->image;
            // Modelに定義済。詳細はdestory確認
            // ファイルが有れば、削除用のファイル名・場所を決めている
            $delete_file_path = $post->image_path;

            //新しいファイル名を追加。新規作成と同じ方法。
            //下記にアクション設定$post->image = date('YmdHis') . '_' . $file->getClientOriginalName();
            $post->image = self::createFileName($file);
        }
        $post->fill($request->all());

        // トランザクション開始
        DB::beginTransaction();
        try {
            // 更新。準備していた記事の情報を保存。
            $post->save();

            if ($file) {
                // 画像アップロード
                $chk = Storage::putFileAs('images/posts', $file, $post->image);
                
                if (!$chk) {
                    // 例外を投げてロールバックさせる。 throw new \Exceptionでエラーを出す。
                    throw new \Exception('画像ファイルの保存に失敗しました。');
                }

                // 画像削除
                if (!Storage::delete($delete_file_path)) {
                    //アップロードした画像を削除する
                    Storage::delete($post->image_path);
                    //例外を投げてロールバックさせる
                    throw new \Exception('画像ファイルの削除に失敗しました。');
                }
            }

            // トランザクション終了(成功)
            DB::commit();
        } catch (\Exception $e) {
            // トランザクション終了(失敗)
            DB::rollback();
            //前のページに戻る。          エラーを出す。
            return back()->withInput()->withErrors($e->getMessage());
        }
        //成功した場合のreturn
        return redirect()->route('posts.show', $post)
            ->with('notice', '記事を更新しました');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        // トランザクション開始
        DB::beginTransaction();
        try {
            $post->delete();

            // 画像削除.$post->image->imade_pathはpost.phpに定義済。
            if (!Storage::delete($post->image_path)) {
                // 例外を投げてロールバックさせる
                throw new \Exception('画像ファイルの削除に失敗しました。');
            }

            // トランザクション終了(成功)
            DB::commit();
        } catch (\Exception $e) {
            // トランザクション終了(失敗)
            DB::rollback();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('posts.index')
            ->with('notice', '記事を削除しました');
    }

    //ファイル名を生成するアクション
    //privateなのでこのクラスからのみ、呼び出せる
    //static なのでどこからでも呼び出せる
    private static function createFileName($file)
    {
        return date('YmdHis') . '_' . $file->getClientOriginalName();
    }
}
