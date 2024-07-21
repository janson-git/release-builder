<?php
/**
 * @var \App\Models\User $user
 */

//use Service\Breadcrumbs\Breadcrumb;
//
//$view->addBreadcrumb(new Breadcrumb('Profile', 'fa-solid fa-user'));
?>
@extends('layout')

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
<div class="card">
    <div class="font-bold">Committer info</div>
    <p class="description">These name and email will be used in merge commits created in this app</p>
    <div>
        @if ($user->committer?->name)
            <i class="fa-solid fa-check is-ok"></i> <span>{{ $user->committer->name }}</span>
        @else
            <i class="fa-solid fa-xmark is-missed"></i> <span>Not set</span>
        @endif
    </div>
    <div>
        @if ($user->committer?->email)
            <i class="fa-solid fa-check is-ok"></i> <span>{{ $user->committer->email }}</span>
        @else
            <i class="fa-solid fa-xmark is-missed"></i> <span>Not set</span>
        @endif
    </div>

    <a href="/user/committer-data" class="mt-4 inline-block text-sky-400 border border-sky-400 hover:bg-sky-400 hover:text-white px-4 py-1 rounded">
        Add Committer Info NI
    </a>

    <div class="mt-8 font-bold">GitHub Personal Access Token</div>
    <p class="mt-2">GitHub fine-granted personal access token. Used to work with repositories via HTTPS protocol</p>
    <div class="mt-2">
        @if ($user->getAccessToken())
            <i class="fa-solid fa-check is-ok"></i> <span>Already uploaded ( expired {{ $user->getAccessTokenExpirationDate() }} )</span>
        @else
            <i class="fa-solid fa-xmark is-missed"></i> <span>Not uploaded</span>
        @endif
    </div>

    <a href="/user/personal-access-token" class="mt-4 inline-block text-sky-400 border border-sky-400 hover:bg-sky-400 hover:text-white px-4 py-1 rounded">
        {{ $user->getAccessToken() ? 'Replace GitHub PAT' : 'Add GitHub PAT' }} NI
    </a>

    <div class="mt-8 font-bold">SSH Key</div>
    <p class="mt-2">Ssh key used to commit your branches to repositories. Also, ssh key allowed to work with repositories via ssh.</p>
    <div>
        @if ($user->hasSshKey())
            <i class="fa-solid fa-check is-ok"></i> <span>Already uploaded</span>
        @else
            <i class="fa-solid fa-xmark is-missed"></i> <span>Not uploaded</span>
        @endif
    </div>

    <a href="/user/add-key" class="mt-4 inline-block text-sky-400 border border-sky-400 hover:bg-sky-400 hover:text-white px-4 py-1 rounded">
        {{ $user->hasSshKey() ? 'Replace SSH Key' : 'Add SSH Key' }}
    </a>

    <br>

    <a href="/auth/logout" class="mt-8 inline-block px-4 py-2 rounded border border-orange-400 text-white bg-orange-400 hover:bg-orange-600">
        Log out
    </a>
</div>
@endsection

