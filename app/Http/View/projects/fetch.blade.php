@extends('./layout.blade.php')

@section('content')
<div>
    <a href="/web/project/show/{{ $pId }}">{{ __('back_to_project') }}</a>
    @foreach ($result as $path => $res)
        <div>{{ $path }} : {{ $res }}</div>
    @endforeach
</div>
@endsection
