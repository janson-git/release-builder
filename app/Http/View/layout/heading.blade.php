<div class="flex justify-between items-center">
    <div>
        @if ($header)<div class="text-2xl font-bold">{!! $header !!}</div>@endif
        @if ($title)<div class="mt-2 italic">{!! $title !!}</div>@endif
    </div>

    @if (isset($linkAction))
        <a class="text-sky-400 border border-sky-400 hover:bg-sky-400 hover:text-white px-4 py-1 rounded" href="{{ $linkAction['path'] }}">{{ $linkAction['caption'] }}</a>
    @endif
</div>
