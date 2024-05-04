<?php
/**
 * @var $project \Service\Project
 * @var $selected
 * @var $action
 * @var $packBranches
 * @var $branches
 * @var $branchesData
 * @var $pack \Service\Pack
 * @var $view \Admin\View
 * @var $title string
 */

use App\Http\Controller\BranchesController as Branches;
use Service\Breadcrumbs\Breadcrumb;
use Service\Breadcrumbs\BreadcrumbsFactory;

$view
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb())
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectPageBreadcrumb($project));

if ($pack) {
    $view->addBreadcrumb(BreadcrumbsFactory::makePackPageBreadcrumb($pack));
}

$view->addBreadcrumb(new Breadcrumb($title));

?>

@extends('./layout.blade.php')

@section('content')
<div class="card">
{{--    <h2 class="font-bold">{{ __('branches') }} <span class="font-normal">({{ count($branches) }})</span></h2>--}}
    <form class="mt-4" action="/branches/save/{{ $project->getId() }}" method="post" onsubmit="return aFilter.checkForm(this);">
        <input type="hidden" name="action" value="{{ $action }}"/>

        @if ($action == Branches::ACTION_PACK_CREATE || $action == Branches::ACTION_PACK_FORK)
            <div class="flex justify-start">
                <input type="text" value="" name="name" placeholder="{{ __('set_pack_name') }}" id="pack-name"/>
                <input type="submit" value="{{ __('save_pack') }}" class="text-orange-400 border bg-orange-400 hover:bg-orange-600 text-white px-4 py-1 rounded"/>
                <span class="btn-action-holder-for-input"></span>
            </div>
        @elseif ($action == Branches::ACTION_PACK_ADD_BRANCH || $action == Branches::ACTION_PACK_CHANGE_BRANCHES)
            <div class="flex justify-start">
                <input type="submit" value="Update package branches" class="text-orange-400 border bg-orange-400 hover:bg-orange-600 text-white px-4 py-1 rounded"/>
                <input type="hidden" name="packId" value="{{ $pack->getId() }}"/>
            </div>
        @endif

        @if ($action == Branches::ACTION_PACK_CHANGE_BRANCHES)
            <input type="hidden" name="oldBranches" value='{{ json_encode($packBranches) }}'/>
        @endif

        <input id="mainInput" type="text" placeholder="{{ __('filter_branches') }}" onkeydown="aFilter.filter()"
               class="mt-6 mb-6 w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
               onkeyup="aFilter.filter()" autofocus/>

        @foreach ($branches as $branch => $repos)
            @if (!$selected || ($selected && isset($selected[$branch])))
                <div class="mt-2 flex justify-start items-center branches-item">
                    <input type="checkbox" name="branches[]" id="br_{{ $branch }}" value="{{ $branch }}"
                           class="checkbox-item"
                           title=""
                           {{ isset($selected[$branch]) ? 'checked' : '' }}
                    />

                    <label class="ml-2" for="br_{{ $branch }}" class="branch-name">{{ $branch }}</label>

                    @if (isset($branchesData[$branch]))
                    <b class="ml-2">{{ array_sum(array_column($branchesData[$branch], 1)) }}</b>
                    @endif

                    @if (isset($branchesData[$branch]))
                        @foreach ($branchesData[$branch] as $repo => $toMasterStatus)
                        <a onclick="$(this).parent().find('div').toggle()">
                            {{ $repo }},
                        </a>
                        @endforeach

                        <div style="display: none; background: #cccccc; padding: 0.2em">
                            @foreach ($branchesData[$branch] as $repo => $toMasterStatus)
                            {{ $toMasterStatus[0] }} < <b>{{ $repo}}</b> > {{ $toMasterStatus[1] }} <br>
                            @endforeach
                        </div>
                    @else
                    <div class="ml-auto repos text-sm">
                        {!! implode(', ', $repos) !!}
                    </div>
                    @endif
                </div>
            @endif
        @endforeach
    </form>
</div>

<div  class="mb-4">
    <a href="/projects/{{ $project->getId() }}/fetch?return=1" class="mr-4 text-orange-400 border border-orange-400 hover:bg-orange-400 hover:text-white px-4 py-1 rounded">
        {{ __('refetch_repositories_and_return') }}
    </a>
    if no branches found
</div>

<script type="text/javascript">
    var aFilter = {
        items: $('.branches-item'),
        input: {},
        version: 1,

        filter: function () {
            var self = this;

            var search = this.input.val().trim();

            localStorage.setItem('search', search);

            var curVersion = ++self.version;

            var searchArray = search.split(' ').map(function (val) {
                return new RegExp(val.trim(), 'ig');
            });

            var text;
            var line;
            var matched = false;

            this.items.each(function (idx, obj) {
                if (curVersion !== self.version) {
                    return;
                }
                line = $(obj);
                text = line.text();
                matched = false;
                var lineMatched = false;

                for (var id in searchArray) {
                    lineMatched = (text.match(searchArray[id]) || line.find('.checkbox-item:checked').length);
                    matched = matched || lineMatched;
                }

                if (matched) {
                    line.removeClass('hidden');
                } else {
                    line.addClass('hidden');
                }
            })
        },
        checkForm: function (form) {
            var formObj = $(form);
            if (formObj.find('#pack-name').length && !formObj.find('#pack-name').val()) {
                alert("Enter pack name, please");
                return false;
            }

            return true;
        },
        init: function () {
            var self = this;
            self.input = $('#mainInput');
            self.input.val(localStorage.getItem('search'));
            self.filter();
        },
        checkAll: function () {
            this.items.not('.closedTab').each(function (idx, obj) {
                obj.attr('checked', true);
            });
        }
    }

    aFilter.init();
</script>
@endsection
