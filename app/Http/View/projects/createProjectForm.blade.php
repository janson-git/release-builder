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
    <style>
        .pads {
            margin: 0.1em 0.1em
        }
    </style>

    <div class="pure-g" style="color: #111">

        <div class="pure-u-1">
            <h2 style="display: inline-block">{{ __('directories') }}</h2> (<a href="?">{{ __('reset') }}</a>)
        </div>

        <form class="pure-u-1">
            @foreach ($dirs as $dirPath)
                <label for="ch_{{ $dirPath }}" class="pure-button pads">
                    <a href="?pack={{ $dirPath }}">{{ $dirPath }}</a>
                    <input type="checkbox" title="{{ $dirPath }}" id="ch_{{ $dirPath }}" name="dirs[]"
                           value="{{ $dirPath }}"/>
                </label>
            @endforeach
            <input type="submit" value="{{ __('assemble_project') }}" class="pure-button btn-primary pads"/>
        </form>

        @if ($passedDirs)
            <div class="pure-u-1"><h2 style="display: inline-block">{{ __('root_directories') }}
                    ({{count($passedDirs) }})</h2> (<a
                        href="./?">{{ __('reset') }}</a>)
            </div>
            <form class="pure-u-1" action="/projects/save">
                <ul>
                    <li>{!! implode('</li><li>', $passedDirs) !!}</li>
                </ul>
                <input type="hidden" name="saveDirs" value='{!! implode(',', $passedDirs) !!}' title=""/>
                <input type="submit" value="{{ __('save_project') }}" class="pure-button btn-primary"/>
            </form>
        @endif


        <div class="pure-u-1"><h2>{{ __('available_repositories') }} ({{ count($node->getRepos()) }})</h2></div>
        <div class="pure-u-1">
            @foreach ($node->getRepos() as $id => $repo)
            <div class="pure-g card">
                <div class="pure-u-1">
                    <b>ID: {{ $dirs[$id] }}</b>
                </div>
                <div class="pure-u-1">
                    <div class="pure-g">
                        <div class="pure-u-1-5">&nbsp</div>
                        <div class="pure-u-4-5">
                            <b>{{ __('branches') }}: {{ count($node->getBranchesByRepoId($id)) }}</b>
                            <br>
                            <div class="pure-g">
                                @foreach ($repo->getRemotesLastChangeTime() as $branch => $time)
                                    <div class="pure-u-2-3 text-overflow-ellipsis">{{ $branch }}</div>
                                    <div class="pure-u-1-3 right">{{ @date('d.M.Y H:i', $time) }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
@endsection
