@extends('layout')

@section('content')

    <form method="post">

        <div class="flex">
            <!-- for checkbox boolean we need to use hidden field for disabled -->
            <input type="hidden" name="is_https_enabled" value="0">
            <input class="mr-2"
                   id="is_https_enabled"
                   type="checkbox"
                   name="is_https_enabled"
                   value="1"
                {{ $settings['is_https_enabled']->value ? 'checked' : '' }}
            >
            <label for="is_https_enabled">Enable HTTPS repositories</label>
        </div>
        <p class="text-gray-600 my-2 ml-6">
            This option enables using HTTPS repositories.
            <br>
            <span class="font-bold">You need to use GitHub Personal Access Token (PAT) for HTTPS repositories.</span>
        </p>


        <div class="mt-4 flex justify-start">
            <button type="submit" class="btn btn-success ">Save</button>
        </div>
    </form>
@endsection
