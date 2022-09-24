{{-- dashboardからコピー --}}
{{-- <x-ファイル名> views~layouts~app.blade.phpに代入 --}}
<x-app-layout>
    {{-- @if (session('notice'))
            <div class="bg-blue-100 border-blue-500 text-blue-700 border-l-4 p-4 my-2">
                {{ session('notice') }}
            </div>
        @endif  --}}
      {{-- @if (session('notice'))
          <div class="bg-blue-100 border-blue-500 text-blue-700 border-l-4 p-4 my-2">
              {{ session('notice') }}
          </div>
      @endif  --}}
       <x-flash-message :message="session('notice')" />   

    {{-- <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{{ $header }}</div> --}}
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Posts.index') }}
        </h2>
    </x-slot> --}}


    {{-- <main>{{ $slot }}</main> --}}
    {{-- <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    記事の一覧画面です！
                </div>
            </div> --}}
            <div class="container max-w-7xl mx-auto px-4 md:px-12 pb-3 mt-3">
                <div class="flex flex-wrap -mx-1 lg:-mx-4 mb-4">
                    @foreach ($posts as $post)
                        <article class="w-full px-4 md:w-1/2 text-xl text-gray-800 leading-normal">
                            <a href="{{ route('posts.show', $post) }}">
                                <h2
                                    class="font-bold font-sans break-normal text-gray-900 pt-6 pb-1 text-3xl md:text-4xl">
                                    {{ $post->title }}</h2>
                                <h3>{{ $post->user->name }}</h3>
                                <p class="text-sm mb-2 md:text-base font-normal text-gray-600">
                                    <span
                                        class="text-red-400 font-bold">{{ date('Y-m-d H:i:s', strtotime('-1 day')) < $post->created_at ? 'NEW' : '' }}</span>
                                    {{ $post->created_at }}
                                </p>
                                <img class="w-full mb-2" src="{{ $post->image_url }}" alt="">

                                {{-- limit:50文字以上なら・・・・表示 --}}
                                <p class="text-gray-700 text-base">{{ Str::limit($post->body, 50) }}</p>
                            </a>
                        </article>
                    @endforeach
                </div>
                {{-- controllerに設定後、
                ビューにページネーションのリンク 例：2枚目、前 --}}
                {{ $posts->links() }}
            </div>
</x-app-layout>
