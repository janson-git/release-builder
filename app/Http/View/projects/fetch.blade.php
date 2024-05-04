@extends('./layout.blade.php')

@section('content')
<div>
    <a class="mb-4" href="/web/project/show/{{ $pId }}">{{ __('back_to_project') }}</a>
    @foreach ($result as $path => $res)
        <div class="mt-2 font-mono">{{ $path }} : {{ $res }}</div>
    @endforeach
</div>
@endsection
