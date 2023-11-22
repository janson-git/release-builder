@extends('./layout.blade.php')

@section('content')
<style type="text/css">
    .config-input {
        width: 100%;
    }
    
    .pure-config-row {
        padding: 0.1em 0em;
    }
</style>

<div class="pure-g">
    <div class="pure-u-1">
        <a href="/config" class="pure-button btn-secondary-outline">
            <i class="fa-solid fa-arrow-left"></i> Return to config list
        </a>
        <a href="/config/settings?scope={{ $scope }}" class="pure-button btn-secondary-outline">Edit current config</a>

        <h1>Configuration: {{ $scope }}</h1>
        <form action="/web/scopes/edit" class="pure-form" method="post">
            <input type="hidden" name="scope" value="{{ $scope }}"/>
            <input type="submit" value="Save" class="pure-button btn-primary"/>

            <a href="#" onclick="addField(true); return false;" class="pure-button">Add field</a>
            
            <div id="edit-from" class="pure-g">
                @foreach ($data as $key => $item)
                    <div class="pure-u-1-2 pure-config-row">
                        <input type="text" placeholder="Marker" value="{{ $key }}" class="config-input"
                               name="data_key[]"/>
                    </div>
                    <div class="pure-u-1-2 pure-config-row">
                        <input type="text" placeholder="Value" value='{{ is_array($item) ? json_encode($item) : $item }}' class="config-input"
                               name="data_value[]"/>
                    </div>
                @endforeach
            </div>
            <input type="submit" value="Save" class="pure-button btn-primary"/>
            <a href="#" onclick="addField(); return false;" class="pure-button">Add field</a>
        </form>
    </div>
    
    <div style="display: none" id="items-template">
            <div class="pure-u-1-2 pure-config-row">
                <input type="text" placeholder="Marker" value="" class="config-input"
                       name="data_key[]"/>
            </div>
            <div class="pure-u-1-2 pure-config-row">
                <input type="text" placeholder="Value" value="" class="config-input"
                       name="data_value[]"/>
            </div>
    </div>
    
    <script type="text/javascript">
        var addField = function (prepend) {
            if (prepend) {
                $('#edit-from').prepend($('#items-template').html());
            } else {
                $('#edit-from').append($('#items-template').html());
            }
        }
    </script>
</div>
@endsection
