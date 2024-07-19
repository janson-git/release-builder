<?php
/**
 * @var $release \App\Models\Release
 */
?>
@extends('layout')

@section('pageActions')
    <x-secondary-page-action href="/releases">Back to releases</x-secondary-page-action>
@endsection

@section('content')
    <span class="mr-4 btn btn-muted-outline font-bold">STATUS BADGE</span>

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
                {{ $release->delivery_date}}
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

        <div class="mt-4 inline-block">
            <a href="/releases/{{ $release->id }}/merge-branches" class="btn btn-primary">Merge branches</a>
            <a href="/releases/{{ $release->id }}/search-conflicts" class="btn btn-secondary">Search conflicts branches</a>
            <a href="/releases/{{ $release->id }}/reset-release-branch" class="btn btn-secondary">Reset release branch</a>
        </div>
    </div>
{{--        --}}



    {{-- TODO: RELEASE ACTIONS HERE: add branches, remove branches, fork release --}}
    <div class="mt-8 mb-4">
        <h3>Branches:</h3>

        <div>
            <div class="w-full mt-2 p-2 font-mono">
                @forelse ($branchesDiffs as $branch => $repos)
                    <div class="flex items-start hover:bg-gray-100">
                        <div class="w-2/3">{{ $branch }}</div>
                        <div class="w-1/3 text-right">
                            <a class="cursor-pointer" onclick="$(this).parent().find('div').toggle()">
                                ({{ count($repos) }})
                                <small>{{array_sum(array_column($repos, 0)) }} < master > {{array_sum(array_column($repos, 1)) }}</small>
                            </a>

                            <div style="display:none" class="text-xs text-gray-800 whitespace-nowrap">
                                @foreach ($repos as $repo => $toMasterStatus)
                                    {{$toMasterStatus[0] }} < <b>{{ $repo}}</b> > {{$toMasterStatus[1] }} <br>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty"><i>No branches added</i></div>
                @endforelse
            </div>
        </div>
    </div>


{{--    @if ($user->owned($pack))--}}
        <div class="mt-8 mb-4 card border-t-2 border-gray-200">
            <h3 class="font-bold">Release actions</h3>

            <div class="mt-4 inline-block">
                <a href="/releases/{{ $release->id }}/edit" class="btn btn-success">Edit release</a>
            </div>
            <div class="mt-4 inline-block">
                <a href="/releases/{{ $release->id }}/fetch-repositories" class="btn">Fetch repositories</a>
            </div>
            <div class="mt-4 inline-block">
                <!-- TODO: need implement popup for getting tags for repositories in release -->
                <a href="#" class="btn" onclick="">Create git tag NI</a>
            </div>
            <div class="mt-4 inline-block">
                <a href="#" class="btn">Push release branch to repository NI</a>
            </div>
{{--            <div class="mt-4 inline-block">--}}
{{--                <a href="#" class="btn">Delete release NI</a>--}}
{{--            </div>--}}
            <div class="mt-4 inline-block">
                <a href="#" class="btn btn-warning">Fork release NI</a>
            </div>
        </div>
{{--    @endif--}}


@endsection
