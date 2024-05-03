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
    <h2 style="display: inline-block">{{ __('directories') }}</h2> (<a href="?">{{ __('reset') }}</a>)

    <form class="pure-u-1">
        @foreach ($dirs as $dirPath)
            <label for="ch_{{ $dirPath }}" class="pure-button pads">
                <input type="checkbox" title="{{ $dirPath }}" id="ch_{{ $dirPath }}" name="dirs[]"
                       value="{{ $dirPath }}"/>
                {{ $dirPath }}
            </label>
        @endforeach
        <input type="submit" value="{{ __('assemble_project') }}" class="pure-button btn-primary pads"/>
    </form>

    <h2>{{ __('available_repositories') }} ({{ count($node->getRepos()) }})</h2>

    @foreach ($node->getRepos() as $id => $repo)
        <div class="card">

            <div class="text-lg font-bold">{{ $dirs[$id] }}</div>
            <div>{{ __('branches') }}: {{ count($node->getBranchesByRepoId($id)) }}</div>
            @foreach ($repo->getRemotesLastChangeTime() as $branch => $time)
                <div class="flex justify-between">
                    <div class="pure-u-2-3 text-overflow-ellipsis">{{ $branch }}</div>
                    <div class="pure-u-1-3 right">{{ @date('d.M.Y H:i', $time) }}</div>
                </div>
            @endforeach
        </div>
    @endforeach
@endsection
