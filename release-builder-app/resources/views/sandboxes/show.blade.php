<?php
/**
 * @var $release \App\Models\Release
 */
?>
@extends('layout')

@section('pageActions')
    <x-secondary-page-action href="/releases/{{ $sandbox->release_id }}">Back to release</x-secondary-page-action>
@endsection

@section('content')
    <div class="card border-t-2 border-gray-200">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <span class="p-1 text-lg">
                    @if($sandbox->status === 'ok')
                        <i class="fa-solid fa-check-circle text-green-600"></i>
                    @else
                        <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                        <b class="ml-2">Sandbox has errors</b>
                    @endif
                </span>
            </div>
        </div>


        <div>
            <h3>Service</h3>

            <div class="mb-4">
                <div>
                    <i class="text-xs fa-solid fa-external-link"></i>
                    <a href="https://github.com/{{ $sandbox->service->repository_name }}" target="_blank" class="ml-1 mr-7 text-blue-400 hover:text-blue-600 hover:underline">
                        {{ $sandbox->service->repository_name }}
                    </a>
                </div>
            </div>
        </div>

{{--        @if ($user->owned($pack))--}}
{{--            @foreach ($checkPoint->getCommands() as $command)--}}
{{--                @include('./components/commandButton.blade.php', ['command' => $command])--}}
{{--            @endforeach--}}
{{--        @endif--}}

        <div class="mt-4 inline-block">
            <a href="/sandboxes/{{ $sandbox->id }}/merge-branches" class="btn btn-primary action-button">Merge branches</a>
            <a href="/sandboxes/{{ $sandbox->id }}/search-conflicts" class="btn btn-secondary action-button">Search conflicts branches</a>
            <a href="/sandboxes/{{ $sandbox->id }}/reset-release-branch" class="btn btn-secondary action-button">Reset release branch</a>
        </div>
    </div>


    <div class="mt-8 mb-4">
        <div>
            <h3 class="inline-block">Branches:</h3>

            <div class="tooltip ml-2">
                <span class="text-cyan-600 inline-block fa fa-solid fa-question-circle"></span>
                <span class="tooltiptext">
                    Every branch shows diff with master: <span class="font-mono font-bold">LEFT < master > RIGHT</span><br>
                    LEFT - commits in master that not presented in work branch<br>
                    RIGHT - commits in branch that not presented in master
                </span>
            </div>
        </div>

        <div>
            <div class="w-full mt-2 p-2 font-mono">
                @forelse (($branchesDiffs ?? []) as $branch => $repos)
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
            <h3 class="font-bold">Sandbox actions</h3>

            <div class="mt-4 inline-block">
                <a href="/sandboxes/{{ $sandbox->id }}/edit" class="btn btn-warning action-button">Manage sandbox branches</a>
                <a href="/sandboxes/{{ $sandbox->id }}/fetch-repository" class="btn action-button">Fetch repository</a>
                <a href="/sandboxes/{{ $sandbox->id }}/push-release-branch" class="btn action-button">Push release branch</a>
            </div>
        </div>
{{--    @endif--}}

@endsection
