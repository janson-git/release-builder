<div class="flex justify-between items-center">
    <div>
        @if ($header)<div class="text-2xl font-bold">{!! $header !!}</div>@endif
        @if ($title)<div class="mt-2 italic">{!! $title !!}</div>@endif
    </div>
</div>

@if (isset($pageMainAction) || isset($pageAction))
<div class="mt-2 flex justify-start items-center">
    @if (isset($pageMainAction))
        <a class="mr-2 px-4 py-1 btn btn-primary" href="{{ $pageMainAction['path'] }}">{{ $pageMainAction['caption'] }}</a>
    @endif
    @if (isset($pageAction))
        <a class="mr-2 px-4 py-1 btn" href="{{ $pageAction['path'] }}">{{ $pageAction['caption'] }}</a>
    @endif
</div>
@endif
