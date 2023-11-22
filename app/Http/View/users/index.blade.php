<?php
/**
 * @var array $userData
 * @var array $projectsData
 * @var array $packsData
 * @var array $branches
 * @var array $data
 * @var \Service\User $user
 * @var $view \Admin\View
 */

use Service\Breadcrumbs\Breadcrumb;

$view->addBreadcrumb(new Breadcrumb('Profile', 'fa-solid fa-user'));
?>

@extends('./layout.blade.php')

@section('content')
<style>
    .is-ok {
        font-size: 1.6em;
        color: #0a0;
        vertical-align: bottom;
    }
    .is-missed {
        font-size: 1.6em;
        color: #a00;
        vertical-align: bottom;
    }
</style>
<div class="content">

    <div class="pure-g">
        <div class="pure-u-1-1">
            <h2>Committer info</h2>
            <p class="description">These name and email will be used in merge commits created in this app</p>
            <div>
                @if ($user->getCommitAuthorName())
                    <i class="fa-solid fa-check is-ok"></i> <span>{{ $user->getCommitAuthorName() }}</span>
                @else
                    <i class="fa-solid fa-xmark is-missed"></i> <span>Not set</span>
                @endif
            </div>
            <div>
                @if ($user->getCommitAuthorEmail())
                    <i class="fa-solid fa-check is-ok"></i> <span>{{ $user->getCommitAuthorEmail() }}</span>
                @else
                    <i class="fa-solid fa-xmark is-missed"></i> <span>Not set</span>
                @endif
            </div>
            <div class="line-separated">
                <a class="pure-button" href="/user/committer-data">
                    {{ __('set_committer') }}
                </a>
            </div>
        </div>

        <div class="pure-u-1-1">
            <div class="pure-menu-separator"></div>
        </div>

        <div class="pure-u-1-1">
            <h2>GitHub Personal Access Token</h2>
            <p class="description">Github fine-granted personal access token. Used to work with repositories via HTTPS protocol</p>
            <div>
                @if ($user->getAccessToken())
                    <i class="fa-solid fa-check is-ok"></i> <span>Already uploaded ( expired {{ $user->getAccessTokenExpirationDate() }} )</span>
                @else
                    <i class="fa-solid fa-xmark is-missed"></i> <span>Not uploaded</span>
                @endif
            </div>

            <div class="line-separated">
                <a class="pure-button" href="/user/personal-access-token">
                    {{ $user->getAccessToken() ? __('replace_pat') : __('add_pat') }}
                </a>
            </div>
        </div>

        <div class="pure-u-1-1">
            <div class="pure-menu-separator"></div>
        </div>

        <div class="pure-u-1-1">
            <h2>SSH Key</h2>
            <p class="description">Ssh key used to commit your branches to repositories. Also, ssh key allowed to work with repositories via ssh.</p>
            <div>
                @if ($sshKeyUploaded)
                    <i class="fa-solid fa-check is-ok"></i> <span>Already uploaded</span>
                @else
                    <i class="fa-solid fa-xmark is-missed"></i> <span>Not uploaded</span>
                @endif
            </div>

            <div class="line-separated">
                <a class="pure-button" href="/user/addkey">
                    {{ $sshKeyUploaded ? __('replace_ssh_key') : __('add_ssh_key') }}
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
