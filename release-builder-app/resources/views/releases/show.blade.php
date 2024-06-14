<?php
/**
 * @var $release \App\Models\Release
 */
?>
@extends('layout')

@section('content')
    <h1>RELEASE: {{ $release->name }}</h1>

    <div class="card border-t-2 border-gray-200">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <span class="font-bold text-xl mr-2">Build</span>
                <span class="p-1 bg-sky-100 text-blue-800">PLACEHOLDER</span>
                <i class="ml-1 fa-regular fa-copy text-gray-800 cursor-pointer" onclick="Clipboard.writeToClipboard('PLACEHOLDER')"></i>

                <span class="ml-4 px-2 py-1 text-xs border bg-green-200 text-gray-600 rounded">
                    active
                </span>
            </div>

            <div class="build-relative-date">
                {{ $release->delivery_date }}
            </div>
        </div>


        <div>
            <h3>Services in release</h3>

            <div class="flex justify-between items-center mb-4">
                @foreach($release->sandboxes as $sandbox)
                    <p>{{ $sandbox->service()->name }}</p>
                @endforeach

                <a href="/releases/{{ $release->id }}" class="pack-link">
                    <i class="fa-regular fa-file-lines"></i> {{ $release->name }}
                </a>
            </div>
        </div>

{{--        @if ($user->owned($pack))--}}
{{--            @foreach ($checkPoint->getCommands() as $command)--}}
{{--                @include('./components/commandButton.blade.php', ['command' => $command])--}}
{{--            @endforeach--}}
{{--        @endif--}}
    </div>
{{--        --}}


{{--        <div class="">Branches</div>--}}
{{--        <ul class="mt-2 p-2 border border-gray-400 overflow-scroll">--}}
{{--            @if (!empty($release->branches))--}}
{{--                @foreach($release->branches as $branch)--}}
{{--                    <li>{{ $branch }}</li>--}}
{{--                @endforeach--}}
{{--            @else--}}
{{--                <li class="empty"><i>No branches added</i></li>--}}
{{--            @endif--}}
{{--        </ul>--}}
@endsection
