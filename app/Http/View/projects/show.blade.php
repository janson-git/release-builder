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

    @if (!$packs)
        <div class="mb-2 text-center">
            <h4>No packages created</h4>
        </div>
    @endif

    @foreach ($packs as $pack)
        <div class="card pack-card mt-6 border-t-2 border-blue-200">
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
                            'classes' => 'right btn-danger-outline',
                        ])
                    @endif
                </div>
            </div>

            <div class="">Branches</div>
            <ul class="mt-2 p-2 border border-gray-400 overflow-scroll">
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

@endsection
