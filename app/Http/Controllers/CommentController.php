<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Post $post)
    {
        return view('comments.create', compact('post'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CommentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request, Post $post)
    // URLに＄post_idが含まれるので、Post $postで受け取る
    {
        // fillableで代入を許可。App-models-comment.phpにfillable記載。
        $comment = new Comment($request->all());
        $comment->user_id = $request->user()->id;
        // ＄post_idは Post $postで受け取った
        // $comment->post_id = $post->id;
        // $comment->save();


        // トランザクション開始 整合性のないことが起こらない
        // DB::beginTransaction();
        try {
            // その記事に関する新しいコメントを登録
            // その投稿の新しいのと書くと、記事に紐付いたIDも自動的に読み取る
            // $comment->post_id = $post->id;
            // $comment->save();                より良い
            $post->comments()->save($comment);

            // トランザクション終了(成功)無くてもいい DB::commit();
            // https://kanda-it-school-kensyu.com/java-basic-contents/jb_ch08/jb_0803/
            // throwableが一番親。Exceptionが子。erroe以外にも投げるもの。
            // throwableは予期せぬエラー。
            // catch文を2つ書くことも可能。
            // } catch (\Exception $e) {    エラー
            // } catch (\throwable $e) {    予期せぬエラー
        } catch (\Throwable $th) {
            // トランザクション終了(失敗) 無くてもいい DB::rollback();
            //                           ログとして詳細なエラー内容を残す。
            //                           ユーザー側にはエラー詳細を伝えない。
            return back()->withInput()->withErrors($th->getMessage());
        }

        return redirect()->route('posts.show', $post)
            ->with('notice', 'コメントを登録しました');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post, Comment $comment)
    {
        return view('comments.edit', compact('post', 'comment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CommentRequest  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(CommentRequest $request, Post $post, Comment $comment)
    {
        //なりすましを確認             updateの内容は＄comment
        if ($request->user()->cannot('update', $comment)) {
            return redirect()->route('posts.show', $post)
                ->withErrors('自分のコメント以外は更新できません');
        }
        
        // storeから変更
        $comment->fill($request->all());

        // トランザクション使用しない  開始 DB::beginTransaction();
        // 一時保管が必要でDBへの処理が大きい。
        try {
            // 更新。準備していた記事の情報を保存。
            $comment->save();

            // トランザクション終了(成功)DB::commit();
        } catch (\Exception $e) {
            // トランザクション終了(失敗) DB::rollback();
            //前のページに戻る。          エラーを出す。
            return back()->withInput()->withErrors($e->getMessage());
        }
        //成功した場合のreturn
        return redirect()->route('posts.show', $post)
            ->with('notice', 'コメントを更新しました');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        //
    }

    private static function createFileName($file)
    {
        return date('YmdHis') . '_' . $file->getClientOriginalName();
    }
}
