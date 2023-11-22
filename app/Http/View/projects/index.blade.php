<?php
/**
 * @var $view \Admin\View
 * @var $branchSets
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
                @if (isset($branchSets[$id]))
                    <div class="pure-u-1">Packs:</div>

                    @foreach ($branchSets[$id] as $packId => $branchData)
                    <div class="pure-u-1 dataset-item">
                        <div>
                            <a href="/packs/{{ $packId }}" class="pack-link">
                                <span class="icon-border"><i class="fa-regular fa-file-lines"></i></span> {{ $branchData['name'] ?? $packId }}
                            </a>

                            <?php $count = count($branchData['branches'] ?? []); ?>

                            @if($count > 0)
                            <span class="tool" data-tip="{!! @implode("\n", @$branchData['branches']) !!}">
                                Branches ({{ $count }}) <i class="fa-solid fa-info-circle"></i>
                            </span>
                            @else
                            <span class="empty"><i>No branches added</i></span>
                            @endif
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