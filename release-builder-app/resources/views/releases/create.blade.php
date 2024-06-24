@extends('layout')

@section('pageActions')
    <x-secondary-page-action href="/releases">Back</x-secondary-page-action>
@endsection

@section('content')
    <h2>Create new release</h2>

    <div class="card">
        <form method="POST" action="/releases">

            <div class="mb-6 mt-4">
                <div class="flex justify-start items-center">
                    <input type="text" value="{{ old('name') }}" name="name" placeholder="Release name" id="release-name"
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
                        @php($checked = in_array($service->id, old('service_ids', [])) ? 'checked="checked"' : '')
                        <input
                            type="checkbox"
                            id="service-{{ $service->id }}"
                            name="service_ids[]"
                            value="{{ $service->id }}"
                            {{ $checked }}
                        />
                        <label for="service-{{ $service->id }}" class="ml-2 cursor-pointer">
                            <span>{{ $service->repository_url }}</span>
                        </label>
                    </div>
                @endforeach

            </div>

            <div class="mb-6">
                <h4 class="mb-2">Branches to release:</h4>
                @if($errors->has('branches'))
                    <div class="text-error">{{ $errors->first('branches') }}</div>
                @endif

                <input id="mainInput" type="text" placeholder="{{ __('filter_branches') }}" onkeydown="aFilter.filter()"
                       class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
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


            </div>

            <div class="mb-4 mt-10">
                <input type="submit" value="Save draft release" class="btn btn-primary cursor-pointer"/>
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

    {{-- FIXME: it is just example of form fields --}}
{{--    <form method="POST" action="/releases">--}}
{{--        @csrf--}}
{{--        <div class="card">--}}
{{--            <div class="mt-4 flex justify-start items-center">--}}
{{--                <div class="w-40">Repository Path </div>--}}
{{--                <input type="text" id="repository_path" name="repository_path" required autocomplete="off"--}}
{{--                       class="ml-4 w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"--}}
{{--                       placeholder="git@github.com:janson-git/release-builder.git"--}}
{{--                />--}}
{{--            </div>--}}
{{--            @if($errors->has('repository_url'))--}}
{{--            <div class="mt-1">--}}
{{--                <span class="ml-40 text-danger">{{ $errors->first('repository_url') }}</span>--}}
{{--            </div>--}}
{{--            @endif--}}

{{--            @if($errors->has('exception'))--}}
{{--            <div class="mt-4">--}}
{{--                <span class="ml-40 text-danger">{{ $errors->first('exception') }}</span>--}}
{{--            </div>--}}
{{--            @endif--}}

{{--            <button--}}
{{--                type="submit"--}}
{{--    --}}{{--            onclick="admin.addRepository(this);"--}}
{{--                class="ml-40 mt-4 block px-4 py-2 rounded border bg-green-400 text-white hover:bg-green-600 hover:text-white"--}}
{{--            >Save</button>--}}
{{--        </div>--}}
{{--    </form>--}}

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

//        const admin = {
//            addRepository: function (btn) {
//                let $repositoryPath = $('#repository_path');
//                let repoPath = $repositoryPath.val();
//
//                if (repoPath.length === 0) {
//                    return false;
//                }
//
//                let _this = this;
//                spinnerOn(btn)
//                $.post(
//                    '/git/add-repository',
//                    { repository_path: repoPath },
//                    function (res) {
//                        window.location.href = '/git';
//                    },
//                    'json',
//                )
//                    .fail(function (jqxhr, textStatus, error) {
//                        console.log(jqxhr, textStatus, error)
//                        _this.log(
//                            jqxhr && jqxhr.responseJSON && jqxhr.responseJSON.error
//                                ? jqxhr.responseJSON.error
//                                : (textStatus + ' ' + error)
//                        );
//                        spinnerOff(btn)
//                    });
//            },
//            log: function (data, el) {
//                $('#doneLog').html('<span class="text-error">' + data + '</span>');
//            }
//        }
    </script>
@endsection
