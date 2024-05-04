<div class="flex justify-between items-center">
    <div>
    @if ($header)
        <div class="text-2xl font-bold">{!! $header !!}</div>
    @endif

    @if ($title)
        <div class="mt-2 text-xl italic">{!! $title !!}</div>
    @endif
    </div>
{{--    <a href="/projects/{{ $id }}" class="text-sky-400 border border-sky-400 hover:bg-sky-400 hover:text-white px-4 py-1 rounded" onclick='admin.update("{{ $dir }}", this)'>View</a>--}}

    @if (isset($linkAction))
        <a class="text-sky-400 border border-sky-400 hover:bg-sky-400 hover:text-white px-4 py-1 rounded" href="{{ $linkAction['path'] }}">{{ $linkAction['caption'] }}</a>
    @endif
</div>
