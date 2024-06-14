<div class="flex justify-between items-start">
    <div>
        @if (isset($header))<div class="text-2xl font-bold">{!! $header !!}</div>@endif
        @if (isset($title))<div class="mt-2 italic">{!! $title !!}</div>@endif
    </div>

    @hasSection('pageActions')
    <div class="flex justify-between items-start">
        @yield('pageActions')
    </div>
    @endif
</div>
