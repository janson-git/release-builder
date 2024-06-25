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

            <div class="mb-4">
                @foreach($release->services as $service)
                    <div class="text-sm">
                        <i class="text-xs fa-solid fa-external-link"></i>
                        <a href="{{ $service->repository_url }}" target="_blank" class="ml-1 mr-7 text-blue-400 hover:text-blue-600 hover:underline">
                            {{ $service->directory }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

{{--        @if ($user->owned($pack))--}}
{{--            @foreach ($checkPoint->getCommands() as $command)--}}
{{--                @include('./components/commandButton.blade.php', ['command' => $command])--}}
{{--            @endforeach--}}
{{--        @endif--}}
    </div>
{{--        --}}


        <div class="">Branches</div>
        <ul class="mt-2 p-2 border border-gray-400 overflow-scroll">
            @forelse ($release->branches as $branch)
                <li>{{ $branch }}</li>
            @empty
                <li class="empty"><i>No branches added</i></li>
            @endforelse
        </ul>
@endsection
