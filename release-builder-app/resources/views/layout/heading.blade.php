<div class="flex justify-between items-start">
    <div>
        @if (isset($header))<div class="text-2xl font-bold">{!! $header !!}</div>@endif
        @if (isset($subheader))<div class="mt-2 italic">{!! $subheader !!}</div>@endif
    </div>

    @hasSection('pageActions')
    <div class="flex justify-between items-start">
        @yield('pageActions')
    </div>
    @endif
</div>
