<?php
/**
 * @var \Service\User $user
 * @var \Admin\View $this
 */
?>

@extends('./layout.blade.php')

@section('content')
    <div class="pure-g">
        <div class="pure-u-1">
            <section class="top-page-nav">
                <a href="/user" class="pure-button btn-secondary-outline btn-s">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('back_to_profile') }}
                </a>
            </section>
        </div>
    </div>

    <div class="pure-g">
        <div class="pure-u-1 description">
            Set username and email which will displayed as commit owner info for pushed release branches
        </div>
        <div class="pure-u-1">
            <form class="pure-form pure-form-aligned" method="post">
                <fieldset>
                    <div class="pure-control-group">
                        <label for="name">Committer name</label>
                        <input id="name" name="name" type="text" value="{{ $user->getCommitAuthorName() }}">
                    </div>

                    <div class="pure-control-group">
                        <label for="email">Committer email</label>
                        <input id="email" name="email" type="email" value="{{ $user->getCommitAuthorEmail() }}">
                    </div>

                    <div class="pure-controls">
                        <button type="submit" class="pure-button pure-button-primary">Save</button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
@endsection
