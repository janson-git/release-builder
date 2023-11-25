<?php
/** @var $view \Admin\View */

use Service\Breadcrumbs\Breadcrumb;

$view
    ->addBreadcrumb(new Breadcrumb('Git', 'fa-solid fa-code-branch', '/git'))
    ->addBreadcrumb(new Breadcrumb('Add repository'));
?>

@extends('./layout.blade.php')

@section('content')
<div class="pure-g">
    <div class="pure-u-1">
        <section class="top-page-nav">
            <a href="/git" class="pure-button btn-secondary-outline btn-s">
                <i class="fa-solid fa-arrow-left"></i> {{ __('back_to_git') }}
            </a>
        </section>
    </div>
</div>

<div class="pure-g">
    <div class="pure-u pure-u-md-1 pure-u-lg-1-2">

        <div class="pure-form pure-form-stacked">
            <fieldset class="pure-group">
                <div class="pure-control-group">
                    <label for="repository_path">Repository Path (HTTPS url or SSH link)</label>
                    <input type="text"
                           id="repository_path"
                           name="repository_path"
                           class="pure-input-1"
                           required />
                </div>

                <button onclick="admin.addRepository(this);"
                        class="pure-input-1 pure-button pure-button-primary">
                    {{ __('save') }}
                </button>
            </fieldset>
        </div>

    </div>

    <div class="pure-u-1">
        <p id="doneLog"></p>
    </div>
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

    const admin = {
        addRepository: function (btn) {
            let $repositoryPath = $('#repository_path');
            let repoPath = $repositoryPath.val();

            if (repoPath.length === 0) {
                return false;
            }

            let _this = this;
            spinnerOn(btn)
            $.post(
                '/git/add-repository',
                { repository_path: repoPath },
                function (res) {
                    window.location.href = '/git';
                },
                'json',
            )
                .fail(function (jqxhr, textStatus, error) {
                    console.log(jqxhr, textStatus, error)
                    _this.log(
                        jqxhr && jqxhr.responseJSON && jqxhr.responseJSON.error
                            ? jqxhr.responseJSON.error
                            : (textStatus + ' ' + error)
                    );
                    spinnerOff(btn)
                });
        },
        log: function (data, el) {
            $('#doneLog').html('<span class="text-error">' + data + '</span>');
        }
    }
</script>
@endsection
