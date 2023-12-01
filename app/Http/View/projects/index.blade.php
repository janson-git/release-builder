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
<div class="pure-g">


@if (!$hasRepos)
    <div class="pure-u-1">
        <span class="pure-button btn-primary" disabled>{{ __('create_project') }}</span>
    </div>
    <div class="pure-u-1">
        <h3 class="text-warning"><i class="fa-solid fa-exclamation-circle"></i> Add repository before creating project</h3>
        <a href="/git/add-repository" class="pure-button btn-secondary">{{ __('add_repository') }}</a>
    </div>
@else
    <div class="pure-u-1">
        <a href="/projects/create-new" class="pure-button btn-primary-outline">{{ __('create_project') }}</a>
    </div>
@endif

    <div class="pure-u-md-1-2 pure-u-xl-2-3">
    @foreach ($projects as $id => $dirs)
        <div class="pure-u-1 card project-card">
            <?php
            $dirs = $dirs ?: [];
            array_walk($dirs, function (&$val) {
                $val = trim($val, '/');
            });
            ?>
            <h1><i class="fa-solid fa-folder"></i> <a href="/projects/{{ $id }}">{{ implode(', ', $dirs) }}</a></h1>

            <div class="pure-g">
            @if (isset($packsByProjects[$id]))
                <div class="pure-u-1">Packs:</div>

                @foreach ($packsByProjects[$id] as $packId => $pack)
                <div class="pure-u-1 dataset-item">
                    <div class="pure-g">
                        <div class="pure-u-2-3">
                            <a href="/packs/{{ $packId }}" class="pack-link">
                                <span class="icon-border"><i class="fa-regular fa-file-lines"></i></span> {{ $pack->getName() }}
                            </a>

                            <?php
                                $count = count($pack->getBranches());
                            ?>
                            @if($count > 0)
                            <span class="tool" data-tip="{!! implode("\n", $pack->getBranches()) !!}">
                                Branches ({{ $count }}) <i class="fa-solid fa-info-circle"></i>
                            </span>
                            @else
                            <span class="empty"><i>No branches added</i></span>
                            @endif
                        </div>
                        <div class="pure-u-1-3">
                            <!-- Here a place to show pack owner name -->
                            @if ($pack->getUser() && !$user->owned($pack))
                                <span class="text-gray-small right">owned by <abbr title="{{ $pack->getUser()->getName() }}">{{ '@' . $pack->getUser()->getLogin() }}</abbr></span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach

            @endif
            </div>
        </div>
    @endforeach
    </div>
</div>
@endsection
