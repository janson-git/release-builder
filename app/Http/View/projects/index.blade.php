<?php
/**
 * @var $view \Admin\View
 * @var $packsByProjects array
 */

use Service\Breadcrumbs\BreadcrumbsFactory;

$view->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb());
?>

@extends('./layout.blade.php')

@section('content')
@if (!$hasRepos)
    <div class="flex justify-start items-center">
        <a href="/git/add-repository" class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200">{{ __('add_repository') }}</a>
        <h3 class="ml-4 text-warning"><i class="fa-solid fa-exclamation-circle"></i> Add repository before creating project</h3>
    </div>
@endif

@foreach ($projects as $id => $dirs)
    <div class="w-full mt-8 card project-card">
        <?php
        $dirs = $dirs ?: [];
        array_walk($dirs, function (&$val) {
            $val = trim($val, '/');
        });
        ?>

        <div class="flex justify-between">
            <div class="font-bold text-lg">
                {{ implode(', ', $dirs) }}
            </div>

            <a href="/projects/{{ $id }}" class="px-4 py-1 btn btn-primary" onclick='admin.update("{{ $dir }}", this)'>View</a>
        </div>

        <div class="mt-2"></div>
        @if (isset($packsByProjects[$id]))
            @foreach ($packsByProjects[$id] as $packId => $pack)
            <div class="mt-1 flex justify-between hover:bg-gray-100">
                <div>
                    <a href="/packs/{{ $packId }}" class="pack-link">
                        <span class="icon-border"><i class="fa-regular fa-file-lines"></i></span> {{ $pack->getName() }}
                    </a>

                    @php($count = count($pack->getBranches()))
                    @if($count > 0)
                    <span class="tool" data-tip="{!! implode("\n", $pack->getBranches()) !!}">
                        Branches ({{ $count }}) <i class="fa-solid fa-info-circle"></i>
                    </span>
                    @else
                    <span class="empty"><i>No branches added</i></span>
                    @endif
                </div>
                <div>
                    <!-- Here a place to show pack owner name -->
                    @if ($pack->getUser() && !$user->owned($pack))
                        <span class="text-gray-small right">owned by <abbr title="{{ $pack->getUser()->getName() }}">{{ '@' . $pack->getUser()->getLogin() }}</abbr></span>
                    @endif
                </div>
            </div>
            @endforeach
        @endif
    </div>
@endforeach
@endsection
