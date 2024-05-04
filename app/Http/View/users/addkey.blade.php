@extends('./layout.blade.php')

@section('content')
<div class="card">
    <h1>{{ $msg }}</h1>
    <form class="pure-form pure-form-aligned" method="post">
        <textarea
                rows="10"
                class="mt-4 border focus:border-black focus:ring-black p-5"
                placeholder="Private ssh key content"
                name="key"
                spellcheck="false"
                style="min-height: 20em; font-size: small;width: 100%; font-family: monospace;"
        ></textarea>

        <div class="mt-4 flex justify-start">
            <button type="submit" class="bg-orange-400 text-white hover:bg-orange-600 hover:text-white px-4 py-1 rounded">{{ __('save') }}</button>
        </div>
    </form>
</div>
@endsection
