<?php
/* @var $project \Service\Project
 * @var $slots \Service\Slot\SlotProto[]
 * @var $fetchCommand \Commands\Command\Project\FetchProjectRepos
 * @var $packs \Service\Pack[]
 * @var $view \Admin\View
 * @var \Service\User $user
 */

use Service\Breadcrumbs\BreadcrumbsFactory;

$view
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb())
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectPageBreadcrumb($project));
?>

@extends('./layout.blade.php')

@section('content')

@foreach ($packs as $pack)
    <div class="card pack-card mt-6">
        <div>
            <?php $branches = $pack->getBranches() ?>

            <div class="flex justify-between items-center mb-4">
                <a href="/packs/{{ $pack->getId() }}" class="pack-link">
                    <i class="fa-regular fa-file-lines"></i> {{ $pack->getName() }}
                </a>

                <!-- Only owned packs allowed to delete -->
                @if ($pack->getUser() && !$user->owned($pack))
                    <span class="text-gray-small right">owned by <abbr title="{{ $pack->getUser()->getName() }}">{{ '@' . $pack->getUser()->getLogin() }}</abbr></span>
                @else
                    @include('./components/commandButton.blade.php', [
                        'command' => $pack->prepareCommand(new \Commands\Command\Pack\RemovePackWithData),
                        'classes' => 'btn-s right btn-danger-outline',
                    ])
                @endif
            </div>
        </div>

        <div class="">Branches</div>
        <ul class="mt-2 branch-list">
            @if (!empty($branches))
                @foreach($branches as $branch)
                    <li>{{ $branch }}</li>
                @endforeach
            @else
            <li class="empty"><i>No branches added</i></li>
            @endif
        </ul>
    </div>
@endforeach

@if (env('ENABLE_DEPLOY'))
<div class="pure-u-md-1-2 pure-u-xl-1-3 vmenu">
    <h3>{{ __('servers') }}</h3>
    <a href="/web/slot/create/?pId={{$id }}" class="pure-button pure-button-primary">{{ __('add_release_server') }}</a>
    <a href="/web/project/slots/{{$id }}" class="pure-button">{{ __('release_servers_list') }}</a>
    <ul>
        @foreach ($slots as $slot)
        <li>{{$slot->getName() }}, {{$slot->getHost().':'.$slot->getPath() }}</li>
        @endforeach
    </ul>
</div>
@endif
@endsection
