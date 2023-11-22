@extends('./layout.blade.php')

@section('content')
<div class="pure-g">
    <div class="pure-u-1">
        <a href="/config" class="pure-button btn-secondary-outline">
            <i class="fa-solid fa-arrow-left"></i> Return to config list
        </a>

        <h1>Edit configuration "{{ $scope }}"</h1>
        @if ($is_exists)
            <form action="/config/settings" class="pure-form" method="post">
                <input type="hidden" name="scope" value="{{ $scope }}"/>
                <input type="hidden" name="action" value="changeName"/>
                <label for="scope-name">Name</label>
                <input type="text" id="scope-name" name="name" value="{{ $scope }}"/>
                <input type="submit" class="pure-button btn-primary" value="Update"/>
            </form>
        <br>
            <form action="/config/settings" class="pure-form" method="post">
                <input type="hidden" name="scope" value="{{ $scope }}"/>
                <input type="hidden" name="action" value="remove"/>
                <input type="submit" class="pure-button btn-danger" value="Delete"/>
            </form>
        @else
            <h2>Данной конфигурации больше не существует</h2>
        @endif
    </div>
</div>
@endsection
