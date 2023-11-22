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
<div class="pure-g">
    <div class="pure-u-1">
        <section class="top-page-nav">
            <a href="/git/add-repository" class="pure-button btn-primary-outline">{{ __('add_repository') }}</a>
        </section>
    </div>
</div>

<div class="pure-g">
    <div class="pure-u-1">
        <table class="pure-table pure-table-bordered shadowed">
            <thead>
            <tr>
                <th>Name</th>
                <th>update</th>
                <th>reset/checkout</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($list as $dir => $data)
                <tr>
                    <td>
                        <div class="badge">
                            <div>{{ strtoupper( $data['type'] ) }}</div>
                        </div>

                        <p style="font-weight: bold; white-space: nowrap">{{ $dir }}</p>
                        <p style="white-space: nowrap; color: #666;">
                            <small>{{ $data['repoName'] }}</small>
                        </p>
                        <small>
                            {{ __('last_update_at') }}:<br/>
                            {{ $data['time']['back'] }}<br/>
                            {{ $data['time']['date'] }}
                        </small>
                    </td>

                    <td>
                        <a class="pure-button" onclick='admin.update("{{ $dir }}", this)'>update</a>
                        <hr/>
                        {!! implode(" <br> ", $data['com']) !!}
                    </td>
                    <td>
                        <a class="pure-button" onclick='admin.fixGit("{{ $dir }}", this)'>reset branch </a>
                        <hr/>
                        <select>
                            @foreach ($data['branch'] as $branch)
                                <option {{ str_starts_with($branch, '*') ? 'selected' : '' }} value="{{ trim($branch, '* ') }}"
                                        title="{{ htmlentities($branch) }}">{{ substr($branch,0, 40) }}</option>
                            @endforeach
                        </select>
                        <a class="pure-button"
                           onclick='admin.checkout("{{ $dir }}", this, $(this).parent().find("select").val())'>checkout
                            branch</a>
                        <hr/>
                        <a onclick="$('.dev-tools-{{ crc32($dir) }}').toggle()">dev tools</a>
                        <div class="dev-tools-{{ crc32($dir) }}" style="display: none">
                            <a class="pure-button" onclick='admin.fixGit("{{ $dir }}", this, 1)'>reset and delete files</a>
                        </div>
                        <hr/>
                        <small>{!! implode(" <br> ", $data['remote']) !!}</small>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="pure-u-1">
        <p id="doneLog"></p>
    </div>
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
            spinnerOn(btn)
            $.getJSON('/git/update/', {dir}, function (res) {
                _this.log(res.data, el);
                spinnerOff(btn);
            }).error(function (res, data, errorThrown) {
                const json = res.responseJSON;
                $('#doneLog').html(
                    `<div class="text-error">${json.code} ${json.reason}</div>` +
                    '<span class="text-error">' + json.message + '</span>'
                );
                spinnerOff(btn)
            });
        },
        log: function (data, el) {
            $('#doneLog').html(data);
            el.find('.upLog').remove();
            data = typeof data == 'string' ? data : JSON.stringify(data);
            el.append('<div class="upLog"><hr/>' + (data && data.substr(0, 150)) + ' <hr/><a href="#doneLog">full log</a></div>');
        }
    }
</script>
@endsection
