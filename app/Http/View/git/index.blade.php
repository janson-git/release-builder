<?php
/**
 * @var $view \Admin\View
 */
$view->addBreadcrumb(
    new \Service\Breadcrumbs\Breadcrumb('Git', 'fa-solid fa-code-branch')
);
?>

@extends('./layout.blade.php')

@section('content')
@foreach ($list as $dir => $data)
<div class="card mb-6">
    <div class="flex justify-start items-center">
        <p class="font-bold text-lg">{{ $dir }}</p>
        <span class="ml-4 inline-block px-1 bg-gray-200 text-xs text-gray-500 rounded">
            {{ $data['type'] }}
        </span>
    </div>

    <div class="text-sm">
        <i class="text-xs fa-solid fa-external-link"></i>
        <a href="https://github.com/{{ $data['repoName'] }}" target="_blank" class="ml-1 mr-7 text-blue-400 hover:text-blue-600 hover:underline">
            {{ $data['repoName'] }}
        </a>
    </div>

    <div class="text-sm">
        <i class="text-xs fa-solid fa-refresh"></i>
        <span class="ml-1">
            {{ $data['time']['back'] }} {{ $data['time']['date'] }}
        </span>
    </div>

    <pre class="mt-6 text-xs font-mono py-4 pl-2 bg-gray-100 border border-gray-600">
        {!! implode("\n", $data['com']) !!}
    </pre>

    <div class="mt-6">
        <div class="flex justify-start">
            <select class="border-b border-black focus:outline-none">
                @foreach ($data['branch'] as $branch)
                    <option {{ str_starts_with($branch, '*') ? 'selected' : '' }} value="{{ trim($branch, '* ') }}"
                            title="{{ htmlentities($branch) }}">{{ substr($branch,0, 40) }}</option>
                @endforeach
            </select>
            <button class="ml-4 bg-gray-100 border border-gray-200 hover:bg-gray-200 text-xs px-4 py-1 rounded" onclick='admin.checkout("{{ $dir }}", this, $(this).parent().find("select").val())'>
                checkout branch
            </button>
        </div>
    </div>

    <div class="mt-4"></div>
    @foreach ($data["remote"] as $remote)
        <div class="text-sm">
            <i class="ml-1 text-xs fa-solid fa-info"></i>
            <span class="ml-2">{{ $remote }}</span>
        </div>
    @endforeach

    <div class="mt-6 flex justify-start">
        <button class="bg-green-400 text-white hover:bg-green-600 hover:text-white px-4 py-1 rounded" onclick='admin.update("{{ $dir }}", this)'>update</button>
        <button class="ml-4 bg-gray-400 text-white hover:bg-gray-600 border border-gray-200 hover:bg-gray-200 px-4 py-1 rounded " onclick='admin.fixGit("{{ $dir }}", this)'>reset branch</button>
        <button class="ml-4 bg-red-400 text-white hover:bg-red-600  px-2 py-1 rounded" onclick='admin.fixGit("{{ $dir }}", this, 1)'>reset and delete files</button>
    </div>
</div>
@endforeach

<div class="fixed bg-white opacity-80 text-gray-900 left-5 text-xs top-40 w-1/5 font-mono">
    <div id="doneLog"></div>
</div>

<script type="text/javascript">
    const admin = {
        fixGit: function (dir, el, realClean) {
            let btn = el;
            realClean = realClean || 0;
            el = $(el).parent();
            let _this = this;
            spinnerOn(btn)
            $.getJSON('/git/reset/', {dir, doClean: realClean}, function (res) {
                _this.log(res.data, el);
                spinnerOff(btn)
            }).error(function (r, data, errorThrown) {
                spinnerOff(btn)
            });
        },

        checkout: function (dir, el, branch) {
            let btn = el;
            el = $(el).parent();
            let _this = this;
            spinnerOn(btn)
            $.getJSON('/git/checkout/', {dir, branch}, function (res) {
                _this.log(res.data, el);
                spinnerOff(btn)
            }).error(function (r, data, errorThrown) {
                spinnerOff(btn)
            });
        },

        update: function (dir, el) {
            let btn = el;
            el = $(el).parent();
            let _this = this;
            // spinnerOn(btn)
            $.getJSON('/git/update/', {dir}, function (res) {
                _this.log(res.data, el);
                // spinnerOff(btn);
            }).error(function (res, data, errorThrown) {
                const json = res.responseJSON;
                $('#doneLog').html(
                    `<div class="text-red-900">${json.code} ${json.reason}</div>` +
                    '<span class="text-red-900">' + json.message + '</span>'
                );
                // spinnerOff(btn)
            });
        },
        log: function (data, el) {
            $('#doneLog').html(data);
        }
    }
</script>
@endsection
