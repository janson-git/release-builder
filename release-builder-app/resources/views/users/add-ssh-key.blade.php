@extends('layout')

@section('pageActions')
    <x-secondary-page-action href="/user">Back To Profile</x-secondary-page-action>
@endsection

@section('content')
<div class="card">
    <form class="pure-form pure-form-aligned" method="post">
        @if($errors->has('key'))
            @foreach($errors->get('key') as $error)
                <p class="text-red-600">{{ $error }}</p>
            @endforeach
        @endif
        <textarea
                rows="10"
                class="mt-4 border focus:border-black focus:ring-black p-5"
                placeholder="PRIVATE ssh key content"
                name="key"
                spellcheck="false"
                style="min-height: 20em; font-size: small;width: 100%; font-family: monospace;"
        ></textarea>

        <div class="mt-4 flex justify-start">
            <button type="submit" class="btn btn-success ">Save</button>
        </div>
    </form>
</div>
@endsection
