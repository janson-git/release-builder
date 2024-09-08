<?php
/**
 * @var $release \App\Models\Release
 */
?>
@extends('layout')

@section('pageActions')
    <x-secondary-page-action href="/sandboxes/{{ $sandbox->id }}">Back to sandbox</x-secondary-page-action>
@endsection

@section('content')
    <div class="card border-t-2 border-gray-200">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <span class="p-1 text-lg">
                    @if($sandbox->status === 'ok')
                        <i class="fa-solid fa-check-circle text-green-600"></i>
                    @else
                        <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                        <b class="ml-2">Sandbox has errors</b>
                    @endif
                </span>
            </div>
        </div>

        <div>
            <h3>Service</h3>

            <div class="mb-4">
                <div>
                    <i class="text-xs fa-solid fa-external-link"></i>
                    <a href="https://github.com/{{ $sandbox->service->repository_name }}"
                       target="_blank"
                       class="ml-1 mr-7 text-blue-400 hover:text-blue-600 hover:underline"
                    >
                        {{ $sandbox->service->repository_name }}
                    </a>
                </div>
            </div>
        </div>
    </div>


    <div class="mb-6 p-2">
        <h4 class="mb-2">Branches to sandbox:</h4>
        @if($errors->has('branches'))
            <div class="text-error">{{ $errors->first('branches') }}</div>
        @endif

        <input id="branches-list-filter"
               type="text"
               placeholder="{{ __('filter_branches') }}"
               class="w-full mb-2 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
               onkeyup="branchesFilter.filter()"
               autofocus/>

        <form method="POST" action="/sandboxes/{{ $sandbox->id }}">

        @foreach ($branches as $branch => $repos)
            @php($checked = in_array($branch, old('branches', $sandbox->branches)))
            @if (!$selected || ($selected && isset($selected[$branch])))
                <div class="mt-2 flex justify-start items-start branches-item hover:bg-gray-100">
                    <div class="w-2/3">
                        <input type="checkbox" name="branches[]" id="br_{{ $branch }}" value="{{ $branch }}"
                               class="checkbox-item"
                               title=""
                            {{ $checked ? 'checked' : '' }}
                        />

                        <label class="ml-2" for="br_{{ $branch }}" class="branch-name">
                            {{ $branch }} <span class="text-gray-400">({{ count($repos) }})</span>
                        </label>

                        @if (isset($branchesDiffs[$branch]))
                            <b class="ml-2 text-gray-400">{{ array_sum(array_column($branchesDiffs[$branch], 1)) }}</b>
                        @endif
                    </div>

                    <div class="w-1/3 text-xs text-gray-600">
                        @if (isset($branchesDiffs[$branch]))
                            @foreach ($branchesDiffs[$branch] as $repo => $toMasterStatus)
                                <a class="cursor-pointer text-black"
                                   onclick="$(this).parent().find('div').toggle()"
                                >
                                    {{ $repo }},
                                </a>
                            @endforeach

                            <div class="bg-gray-200 p-1 pl-4 text-gray-800" style="display: none;">
                                @foreach ($branchesDiffs[$branch] as $repo => $toMasterStatus)
                                    {{ $toMasterStatus[0] }} < <b>{{ $repo}}</b> > {{ $toMasterStatus[1] }} <br>
                                @endforeach
                            </div>
                        @else
                            <div class="ml-auto repos">
                                {!! implode(', ', $repos) !!}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach

            <div class="mb-4 mt-10">
                <input type="submit" value="Save changes" class="btn btn-primary cursor-pointer"/>
                <span class="btn-action-holder-for-input"></span>
            </div>
        </form>
    </div>

    <script type="text/javascript">
        const branchesFilter = BranchesFilter.init('sandbox_{{ $sandbox->id }}');
    </script>
@endsection
