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
    <div class="card border-t-2 border-gray-200">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <span class="font-bold text-xl mr-2">Build</span>
                <span class="p-1 bg-sky-100 text-blue-800">{{ $release->release_branch_name }}</span>
                <i class="ml-1 fa-regular fa-copy text-gray-800 cursor-pointer" onclick="Clipboard.writeToClipboard('{{ $release->release_branch_name }}')"></i>

{{--                <span class="ml-4 px-2 py-1 text-xs border bg-green-200 text-gray-600 rounded">--}}
{{--                    active--}}
{{--                </span>--}}
            </div>

            <div class="build-relative-date">
                {{ $release->delivery_date}}
            </div>
        </div>


        <div>
            <h3>Services in release</h3>

            <div class="mb-4">
                @php
                $sandboxes = $release->sandboxes->keyBy('service_id');
                @endphp

                <table>
                @foreach($release->services as $service)
                    <tr>
                        <td>
                            <i class="text-xs fa-solid fa-external-link"></i>
                            <a href="https://github.com/{{ $service->repository_name }}" target="_blank" class="ml-1 mr-7 text-blue-400 hover:text-blue-600 hover:underline">
                                {{ $service->repository_name }}
                            </a>
                        </td>
                        <td>
                            @php($status = $sandboxes->get($service->id)->status)
                            @if($status === 'ok')
                                <i class="fa-solid fa-check-circle text-green-600"></i>
                                <a href="/sandboxes/{{ $sandboxes->get($service->id)->id }}" class="ml-4 btn btn btn-s">Go to sandbox</a>
                            @else
                                <i class="fa-solid fa-circle-exclamation text-red-600" title="Sandbox has errors"></i>
                                <a href="/sandboxes/{{ $sandboxes->get($service->id)->id }}" class="ml-4 btn btn-warning btn-s">Go to sandbox</a>
                            @endif
                        </td>
                    </tr>

                @endforeach
                </table>
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
                <a href="/releases/{{ $release->id }}/push-release-branch" class="btn">Push release branch to repositories</a>
            </div>
            <div class="mt-4 inline-block">
                <a href="#" class="btn btn-warning">Fork release NI</a>
            </div>

            <br>
            <div class="mt-4 inline-block">
                <form method="POST" action="/releases/{{ $release->id }}">
                    @csrf
                    @method('DELETE')
                    <input type="submit"
                           onclick="return confirm('Are you really want to delete \'{{ $release->name }}\' release?')"
                           class="btn btn-danger"
                           value="Delete release"
                    >
                </form>
            </div>
        </div>
{{--    @endif--}}


@endsection
