@extends('./layout.blade.php')

@section('content')
<h1>Configuration control</h1>
<form action="/config/edit" class="pure-form" method="get">
    <label for="scope-name">New configuration</label>
    <input type="text" id="scope-name" name="scope"/>
    <input type="submit" value="Create" class="pure-button btn-primary"/>
</form>

<h2>Config list:</h2>
<ul>
    @foreach ($scopes as $scope)
        <li><a href="/config/edit?scope={{ $scope }}">{{ $scope }}</a></li>
    @endforeach
</ul>
@endsection
