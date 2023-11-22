<?php
/**
 * @var $id
 * @var $sandboxReady
 * @var $branches
 * @var $pId
 * @var $pack \Service\Pack
 * @var $view \Admin\View
 */

use Service\Breadcrumbs\BreadcrumbsFactory;

$view
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb())
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectPageBreadcrumb($pack->getProject()))
    ->addBreadcrumb(BreadcrumbsFactory::makePackPageBreadcrumb($pack));
?>

@extends('./layout.blade.php')

@section('content')
<style>
    .pure-button {
        margin-top: 0.3em;
    }
    .btn-danger {
        margin-top: 0.8em;
    }
    h2 {
        padding-left: 0.7em;
    }
    .inactive {
        color: #888;
    }
    .separator {
        border-bottom: 1px solid #999;
        height: 7px;
        margin-bottom: 5px;
    }
</style>

<div class="pure-g">
    <div class="pure-u-1">
        <section class="top-page-nav">
            <a href="/projects/{{ $pack->getProject()->getId() }}" class="pure-button btn-secondary-outline btn-s">
                <i class="fa-solid fa-arrow-left"></i> {{ __('back_to_project') }}
            </a>
        </section>
    </div>
</div>

<div class="pure-g">

    <div class="pure-u-1 pure-u-md-2-3 bset">
        <h3>{{ __('builds') }}</h3>
        <div class="pure-g">

            @php($lastCheckpointId = $pack->getLastCheckPoint()->getName())

            @foreach ($pack->getCheckPoints() as $cpId => $checkPoint)
                @php( $className = ($lastCheckpointId === $cpId ? ' active ' : ''))
                <div class="pure-u-1 pure-u-lg-1-2 pure-u-xl-1-3 card build-card {{ $className }}">
                    <div class="build-card-content">
                        <div class="build-relative-date">{{ $checkPoint->getDetails()['relativeDate'] }}</div>

                        <div>{{ $cpId }}</div>
                        <div class="separator"></div>

                        @foreach ($checkPoint->getCommands() as $command)
                            @include('./components/commandButton.blade.php', [
                                'command' => $command,
                            ])
                            <br>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if (env('ENABLE_DEPLOY'))
    <div class="pure-u-1 pure-u-md-1-3 bset">
        <h3>{{ __('deploy') }}</h3>
        @if ($lastCheckpoint = $pack->getLastCheckPoint())
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

    <div class="pure-u-1 pure-u-md-2-3 bset">
        <h3>{{ __('branches_in_pack') }} ({{ count($branches) }})</h3>

        <a href="/branches/add/{{ $pId }}/{{ $id }}" class="pure-button btn-primary">Add branches</a>
        <a href="/branches/remove/{{ $pId }}/{{ $id }}" class="pure-button ">Remove branches</a>
        <a href="/branches/fork-pack/{{ $pId }}/{{ $id }}" class="pure-button ">Fork pack</a>
        <ul>
            @foreach ($branches as $branchName => $repos)
                <li class="{{ !$repos ? 'inactive' : '' }}">{{ $branchName }}
                    <a style="cursor: pointer;"
                       onclick="$(this).parent().find('div').toggle()">
                        ({{ count($repos) }}) <small>{{array_sum(array_column($repos, 0)) }} < master > {{array_sum(array_column($repos, 1)) }}</small>
                    </a>
                    <div style="display: none; background: #cccccc; padding: 0.2em">
                        @foreach ($repos as $repo => $toMasterStatus)
                            {{$toMasterStatus[0] }} < <b>{{ $repo}}</b> > {{$toMasterStatus[1] }} <br>
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="pure-u-1 pure-u-md-1-3 bset">
        <h3>{{ __('pack_controls') }}</h3>
        @foreach ($pack->getPackCommands() as $command)
            <div>
            @if($command->hasQuestion())
                @include('./components/commandButtonWithQuestion.blade.php', ['command' => $command])
            @else
                @include('./components/commandButton.blade.php', ['command' => $command])
            @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
