<div class="flex justify-between items-start">
    <div>
        @if ($header)<div class="text-2xl font-bold">{!! $header !!}</div>@endif
        @if ($title)<div class="mt-2 italic">{!! $title !!}</div>@endif
    </div>

    @if (isset($pageMainAction) || isset($pageAction))
    <div class="flex justify-between items-start">
        @if (isset($pageMainAction))
            <a class="mr-2 px-4 py-1 btn btn-primary-outline" href="{{ $pageMainAction['path'] }}">{{ $pageMainAction['caption'] }}</a>
        @endif
        @if (isset($pageAction))
            <a class="mr-2 px-4 py-1 btn btn-muted" href="{{ $pageAction['path'] }}">{{ $pageAction['caption'] }}</a>
        @endif
    </div>
    @endif
</div>


