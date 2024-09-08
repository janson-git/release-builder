@extends('layout')

@section('pageActions')
    <x-secondary-page-action href="/releases/{{ $release->id }}">Back</x-secondary-page-action>
@endsection

@section('content')
    <h2>Edit release</h2>

    <div class="card">
        <form method="POST" action="/releases/{{ $release->id }}">

            <div class="mb-6 mt-4">
                <div class="flex justify-start items-center">
                    <input type="text" value="{{ old('name', $release->name) }}" name="name" placeholder="Release name" id="release-name"
                           class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
                    />
                </div>
                @if($errors->has('name'))
                    <div class="text-error">{{ $errors->first('name') }}</div>
                @endif
            </div>

            <div class="mb-6">
                <h4 class="mb-2">Services to release:</h4>
                @if($errors->has('service_ids'))
                    <div class="text-error">{{ $errors->first('service_ids') }}</div>
                @endif

                @foreach($servicesList as $service)
                    <div class="flex justify-start">
                        @php($checked = in_array($service->id, old('service_ids', $release->services->pluck('id')->toArray())))
                        <input
                            type="checkbox"
                            id="service-{{ $service->id }}"
                            name="service_ids[]"
                            value="{{ $service->id }}"
                            {{ $checked ? 'checked' : '' }}
                        />
                        <label for="service-{{ $service->id }}" class="ml-2 cursor-pointer">
                            <span>{{ $service->repository_name }}</span>
                        </label>
                    </div>
                @endforeach

            </div>

            <div class="mb-6">
                <h4 class="mb-2">Branches to release:</h4>
                @if($errors->has('branches'))
                    <div class="text-error">{{ $errors->first('branches') }}</div>
                @endif

                <input id="branches-list-filter"
                       type="text"
                       placeholder="{{ __('filter_branches') }}"
                       class="w-full mb-2 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
                       onkeyup="branchesFilter.filter()" autofocus/>

                @foreach ($branches as $branch => $repos)
                    @php($checked = in_array($branch, old('branches', $release->branches->getCommonBranches())))
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
                                        <a class="cursor-pointer text-black" onclick="$(this).parent().find('div').toggle()">
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

            </div>

            <div class="mb-4 mt-10">
                <input type="submit" value="Save changes" class="btn btn-primary cursor-pointer"/>
                <span class="btn-action-holder-for-input"></span>
            </div>
        </form>
    </div>

    <div  class="mb-4">
        <a href="#" class="mr-4 text-orange-400 border border-orange-400 hover:bg-orange-400 hover:text-white px-4 py-1 rounded">
            Fetch repositories and return
        </a>
        if no branches found
    </div>

    <div class="font-mono text-xs">
        <p id="doneLog"></p>
    </div>

    @if (isset($result))
        <h2>Result</h2>
        <table class="pure-table">
            @foreach ($result as $command)
                <tr>
                    <td>{{ $command['com'] }}</td>
                    <td>{{ $command['res'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <script type="text/javascript">
        const branchesFilter = BranchesFilter.init('release_{{ $release->id }}');
    </script>
@endsection
