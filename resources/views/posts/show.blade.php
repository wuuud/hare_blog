<x-app-layout>
    {{-- class= 以降でCSSを設定 --}}
    {{-- PostContollerのshow  noticeについてifで書く
            return redirect()
            ->route('posts.show', $post)
            //withでメッセージを出す with(メッセージのキー, メッセージのvalue)
            ->with('notice', '記事を登録しました'); --}}
    <div class="container lg:w-3/4 md:w-4/5 w-11/12 mx-auto my-8 px-8 py-4 bg-white shadow-md">
        <x-flash-message :message="session('notice')" />



        {{-- @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 my-2" role="alert">
                <p>
                    <b>{{ count($errors) }}件のエラーがあります。</b>
                </p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif --}}

        {{-- />とすることで1つの＜＞で完了できる --}}
        <x-validation-errors :errors="$errors" />
        {{-- </x-validation-errors> --}}

        <article class="mb-2">
            <h2 class="font-bold font-sans break-normal text-gray-900 pt-6 pb-1 text-3xl md:text-4xl">{{ $post->title }}
            </h2>
            {{-- 投稿に紐付いたユーザーの名前 nameをemailに変えることで違う情報も取れる --}}
            <h3>{{ $post->user->name }}</h3>
            <p class="text-sm mb-2 md:text-base font-normal text-gray-600">
                {{--                                             一日前と比較して新しいなら      NEW        三項演算子(判定条件 ? trueの時の処理 : falseの時の処理)              --}}
                <span
                    class="text-red-400 font-bold">{{ date('Y-m-d H:i:s', strtotime('-1 day')) < $post->created_at ? 'NEW' : '' }}</span>
                {{ $post->created_at }}
            </p>
            {{-- 下記のenv('APP_URL')は APP_URL=http://localhost
            {{ config-filesystem.phpの 'disks' => 
            'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            {{-- 'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            ], --}}
            {{-- post.phpに書いたので下記を短縮できる
            <img src="{{ Storage::url('images/posts/' . $post->image) }}" alt="" class="mb-4"> --}}
            {{-- Post.phpに書いたので()不要。<img src="{{ $post->image_url() }}" alt="" class="mb-4"> --}}
            <img src="{{ $post->image_url }}" alt="" class="mb-4">
            <p class="text-gray-700 text-base">{!! nl2br(e($post->body)) !!}</p>
        </article>
        <div class="flex flex-row text-center my-4">
            {{-- <a href="{{ route('posts.edit', $post) }}"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-20 mr-2">編集</a>
            <form action="{{ route('posts.destroy', $post) }}" method="post">
                @csrf
                @method('DELETE')
                <input type="submit" value="削除" onclick="if(!confirm('削除しますか？')){return false};"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-20">
            </form> --}}

            {{-- @can('update', $post)
                // 自分が投稿した記事の場合
                @else
                // 他人が投稿した記事の場合または非ログインの場合
                @endcan 
                下記の設定により、他人のログイン時には編集・削除ボタンは表示なし --}}
            @can('update', $post)
                <a href="{{ route('posts.edit', $post) }}"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-20 mr-2">編集</a>
            @endcan
            @can('delete', $post)
                <form action="{{ route('posts.destroy', $post) }}" method="post">
                    @csrf
                    @method('DELETE')
                    <input type="submit" value="削除" onclick="if(!confirm('削除しますか？')){return false};"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-20">
                </form>
            @endcan
        </div>
        {{-- ⑧comment 登録追加 --}}
        @auth
            <hr class="my-4">

            <div class="flex justify-end">
                {{-- comment用に作成したROUTEから  name        idの指定。URLに必要  --}}
                <a href="{{ route('posts.comments.create', $post) }}"
                    class="bg-indigo-400 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline block">コメント登録</a>
            </div>
        @endauth


        {{-- ⑨comment 一覧機能用追加 --}}
        <section class="font-sans break-normal text-gray-900 ">
            @foreach ($comments as $comment)
                <div class="my-2">
                    <span class="font-bold mr-3">{{ $comment->user->name }}</span>
                    <span class="text-sm">{{ $comment->created_at }}</span>
                    {{-- エスケープされないようにLaravel5では、{!! !!}で囲む必要があります。
                    ただ、そうすると元々あったタグまでもエスケープされずに表示されるので、最初にe()でエスケープを行います。
                    1.e() でエスケープをする
                    2.nl2br() で改行を<br>に置き換える
                    3.{!! !!}で、<br>だけエスケープをせずに表示する --}}
                    <p>{!! nl2br(e($comment->body)) !!}</p>

                    {{-- 10 comment 編集削除 --}}
                    <div class="flex justify-end text-center">
                    {{-- can endcanがふたつ ①update ②delete--}}
                        @can('update', $comment)
                            {{-- 編集  表示出るか試しに記載しネットで確認 --}}
                            {{--                 routeで$post記事,$commentコメントのIDの両方がほしい 
                                                2つ欲しい場合はは配列でまとめる。routeingm自体を変更するのもあり。--}}
                            <a href="{{ route('posts.comments.edit', [$post, $comment]) }}"
                                class="text-sm bg-green-400 hover:bg-green-600 text-white font-bold py-1 px-2 rounded focus:outline-none focus:shadow-outline w-20 mr-2">編集</a>
                        @endcan
                        @can('delete', $comment)
                            {{-- 削除 表示出るか試しに記載しネットで確認--}}
                            <form action="{{ route('posts.comments.destroy', [$post, $comment]) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <input type="submit" value="削除" onclick="if(!confirm('削除しますか？')){return false};"
                                    class="text-sm bg-red-400 hover:bg-red-600 text-white font-bold py-1 px-2 rounded focus:outline-none focus:shadow-outline w-20">
                            </form>
                        @endcan
                    </div>
                </div>
                <hr>
            @endforeach
        </section>

    </div>
</x-app-layout>
