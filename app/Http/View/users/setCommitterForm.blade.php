<?php
/**
 * @var \Service\User $user
 * @var \Admin\View $this
 */
?>

@extends('./layout.blade.php')

@section('content')
    <div>
        Set username and email which will display as a commit owner info for pushed release branches
    </div>

    <form class="mt-6 card bg-white p-8" method="post">
        <div class="flex justify-left">
            <label for="name" class="w-40">Committer name</label>
            <input class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none" id="name" name="name" type="text" value="{{ $user->getCommitAuthorName() }}">
        </div>

        <div class="mt-4 flex justify-left">
            <label for="email" class="w-40">Committer email</label>
            <input class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none" id="email" name="email" type="email" value="{{ $user->getCommitAuthorEmail() }}">
        </div>

        <button type="submit" class="mt-4 block px-4 py-2 rounded border border-green-400 text-green-400 hover:bg-green-400 hover:text-white">Save</button>
    </form>
@endsection
