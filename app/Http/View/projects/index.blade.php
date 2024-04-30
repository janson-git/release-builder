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
<div>
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
            <div class="text-lg">
                <i class="text-gray-800 fa-solid fa-folder"></i>
                <a class="ml-2" href="/projects/{{ $id }}">{{ implode(', ', $dirs) }}</a>
            </div>

            @if (isset($packsByProjects[$id]))
                <div class="mt-1">
                @foreach ($packsByProjects[$id] as $packId => $pack)
                <div class="mt-1">
                    <div class="pure-g">
                        <div class="group/item pure-u-2-3">
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
                </div>
            @endif
        </div>
    @endforeach
    </div>
</div>
@endsection
