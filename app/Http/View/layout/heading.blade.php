<div class="flex justify-between items-center">
    <div>
    @if ($header)
            <div class="text-2xl font-bold">{!! $header !!}</div>
    @endif

    @if ($title)
        <div class="mt-2 text-xl italic">{!! $title !!}</div>
    @endif
    </div>
    @if ($action)<a class="mr-4 text-blue-400 hover:text-blue-600 hover:underline" href="{{ $action['path'] }}">{{ $action['caption'] }}</a>@endif
</div>
