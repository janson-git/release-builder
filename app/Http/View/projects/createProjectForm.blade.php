<?php
/**
 * @var $view \Admin\View
 */

use Service\Breadcrumbs\Breadcrumb;
use Service\Breadcrumbs\BreadcrumbsFactory;

$view
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb())
    ->addBreadcrumb(new Breadcrumb('Create new', 'fa-solid fa-folder-open', '/projects/create-new'));
?>
@extends('./layout.blade.php')

@section('content')

    @if ($passedDirs)
        <div class="card">
            <h2 class="mb-2 inline-block">
                {{count($passedDirs) }} selected root directories for project
            </h2>
            <a href="/projects/create-new" class="ml-3 text-blue-600 underline">Reset</a>


        <form action="/projects/save">
            <ul>
                <li>{!! implode('</li><li>', $passedDirs) !!}</li>
            </ul>
            <input type="hidden" name="saveDirs" value='{!! implode(',', $passedDirs) !!}' title=""/>
            <input type="submit" value="{{ __('save_project') }}" class="mt-2 btn btn-primary"/>
        </form>
        </div>
    @endif

    <form class="card">
        <h2 class="mb-2 inline-block">Directories</h2>
        <a href="/projects/create-new" class="ml-3 text-blue-600 underline">Reset</a>

        @foreach ($dirs as $dirPath)
            <label for="ch_{{ $dirPath }}" class="block mt-1">
                <input type="checkbox" title="{{ $dirPath }}" id="ch_{{ $dirPath }}" name="dirs[]" value="{{ $dirPath }}"/>
                <a class="ml-1 underline text-blue-600" href="?pack={{ $dirPath }}">{{ $dirPath }}</a>
            </label>
        @endforeach

        <input type="submit" value="{{ __('assemble_project') }}"
               class="mt-4 block px-4 py-2 rounded border bg-green-400 text-white hover:bg-green-600 hover:text-white"
        />
    </form>


    @if (!$node->getRepos())
        <h2 class="mt-8 mb-8 font-bold">
            No repositories found in this folder
        </h2>
    @else
        <h2 class="mt-8 font-bold">
            {{ count($node->getRepos()) }} repositories available
        </h2>

        @foreach ($node->getRepos() as $id => $repo)
            <div class="card mt-4">

                <div class="mt-2"><i class="fa fa-folder-open"></i> {{ $dirs[$id] }}</div>
                <div class="mt-2 mb-4"><i class="fa fa-code-branch"></i> {{ count($node->getBranchesByRepoId($id)) }}</div>

                @foreach ($repo->getRemotesLastChangeTime() as $branch => $time)
                    <div class="flex justify-between">
                        <div>{{ $branch }}</div>
                        <div>{{ @date('d.M.Y H:i', $time) }}</div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

@endsection
