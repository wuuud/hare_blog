{{-- dashboardからコピー --}}
{{-- <x-ファイル名> views~layouts~app.blade.phpに代入 --}}
<x-app-layout>
{{-- <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{{ $header }}</div> --}}
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Posts.index') }}
        </h2>
    </x-slot> --}}
    

{{-- <main>{{ $slot }}</main> --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    記事の一覧画面です！
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
