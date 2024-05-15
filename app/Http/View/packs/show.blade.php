<?php
/**
 * @var $id
 * @var $sandboxReady
 * @var $branches
 * @var $pId
 * @var $pack \Service\Pack
 * @var $view \Admin\View
 * @var $user \Service\User
 */

use Service\Breadcrumbs\BreadcrumbsFactory;

$view
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb())
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectPageBreadcrumb($pack->getProject()))
    ->addBreadcrumb(BreadcrumbsFactory::makePackPageBreadcrumb($pack));
?>

@extends('./layout.blade.php')

@section('content')
<div>
    @if (!$user->owned($pack))
        <div class="mb-4 border border-orange-400 rounded px-4 py-1 text-center">
            <div class="text-xl text-orange-400">
                <i class="fa-solid fa-warning icon"></i> Actions are available only for package owners!
            </div>
            <div class="text-orange-400">If you need to handle this package you should fork it.</div>
        </div>
    @endif

    @php($lastCheckpointId = $pack->getLastCheckpoint()->getName())
    @foreach ($pack->getCheckpoints() as $cpId => $checkPoint)
        @php( $className = ($lastCheckpointId === $cpId ? ' active ' : ''))
        <div class="card border-t-2 border-gray-200">
            <div class="mb-4 flex justify-between items-center">
                <div class="{{ $lastCheckpointId === $cpId ? '' : 'text-gray-400' }}">
                    <span class="font-bold text-xl mr-2">Build</span>
                    <span class="p-1 bg-sky-100 text-blue-800">{{ $cpId }}</span>
                    <i class="ml-1 fa-regular fa-copy text-gray-800 cursor-pointer" onclick="Clipboard.writeToClipboard('{{ $cpId }}')"></i>

                    @if ($lastCheckpointId === $cpId)
                        <span class="ml-4 px-2 py-1 text-xs border bg-green-200 text-gray-600 rounded">
                            active
                        </span>
                    @endif
                </div>

                <div class="build-relative-date">
                    {{ $checkPoint->getDetails()['relativeDate'] }}
                </div>
            </div>

            @if ($user->owned($pack))
                @foreach ($checkPoint->getCommands() as $command)
                    @include('./components/commandButton.blade.php', ['command' => $command])
                @endforeach
            @endif
        </div>
    @endforeach

    @if (env('ENABLE_DEPLOY'))
    <div class="card mt-4">

        <h3 class="font-bold">{{ __('deploy') }}</h3>
        @if ($lastCheckpoint = $pack->getLastCheckpoint())
            <div>{{ $lastCheckpoint->getName() }}</div>
            <div class="separator"></div>
            @foreach ($pack->getDeployCommands() as $command)
                <div>
                    @include('./components/commandButton.blade.php', ['command' => $command])
                </div>
            @endforeach
        @endif
    </div>
    @endif

    <div class="mt-8">
        <h3 class="font-bold mb-4">Branches in package</h3>

        @if($user->owned($pack))
            <a href="/branches/add/{{ $pId }}/{{ $id }}" class="btn btn-warning-outline">Add branch</a>
            <a href="/branches/remove/{{ $pId }}/{{ $id }}" class="btn btn-warning-outline">Remove branch</a>
        @endif
        <a href="/branches/fork-pack/{{ $pId }}/{{ $id }}" class="btn btn-muted-outline">Fork pack</a>

        <div class="mt-4">
            @foreach ($branches as $branchName => $repos)
            <div class="px-2 flex justify-between hover:bg-gray-100 font-mono {{ !$repos ? 'inactive' : '' }}">{{ $branchName }}
                <a class="cursor-pointer" onclick="$(this).parent().find('div').toggle()">
                    ({{ count($repos) }})
                    <small>{{array_sum(array_column($repos, 0)) }} < master > {{array_sum(array_column($repos, 1)) }}</small>
                </a>

                <div style="display:none" class="font-normal">
                    @foreach ($repos as $repo => $toMasterStatus)
                        {{$toMasterStatus[0] }} < <b>{{ $repo}}</b> > {{$toMasterStatus[1] }} <br>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @if ($user->owned($pack))
    <div class="mt-8 mb-4">
        <h3 class="font-bold">Actions</h3>

        @foreach ($pack->getPackCommands() as $command)
            <div class="mt-4">
                @if($command->hasQuestion())
                    @include('./components/commandButtonWithQuestion.blade.php', ['command' => $command])
                @else
                    @include('./components/commandButton.blade.php', ['command' => $command])
                @endif
            </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
