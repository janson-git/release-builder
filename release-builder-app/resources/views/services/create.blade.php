@extends('layout')

@section('pageActions')
    <x-secondary-page-action href="/services">Back</x-secondary-page-action>
@endsection

@section('content')
    <form method="POST" action="/services">
        @csrf
        <div class="card">
            <div class="mt-4 flex justify-start items-center">
                <div class="w-40">Repository Path </div>
                <input type="text" id="repository_path" name="repository_path" required autocomplete="off"
                       class="ml-4 w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
                       placeholder="git@github.com:janson-git/release-builder.git"
                />
            </div>
            @if($errors->has('repository_url'))
            <div class="mt-1">
                <span class="ml-40 text-danger">{{ $errors->first('repository_url') }}</span>
            </div>
            @endif

            @if($errors->has('exception'))
            <div class="mt-4">
                <span class="ml-40 text-danger">{{ $errors->first('exception') }}</span>
            </div>
            @endif

            <button
                type="submit"
    {{--            onclick="admin.addRepository(this);"--}}
                class="ml-40 mt-4 block px-4 py-2 rounded border bg-green-400 text-white hover:bg-green-600 hover:text-white"
            >Save</button>
        </div>
    </form>

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
