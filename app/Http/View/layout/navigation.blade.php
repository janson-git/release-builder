<div class="mt-12 flex justify-start">
    {{--                <a class="pure-menu-heading" href="/user">{{ $user?->getLogin() }}</a>--}}
    @foreach ( $mainMenu as $menuItem)
        <?php /** @var $menuItem \Service\Menu\MenuItem */ ?>
        <a class="px-6 py-2 hover:underline {!! $menuItem->isSelected() ? 'bg-gray-600 text-white' : '' !!}" href="{{$menuItem->route }}">
            @if ($menuItem->iconClass)
                <i class="{{ $menuItem->iconClass }} icon"></i>
            @else
                <i class="fa-solid icon"></i>
            @endif
            <span class="ml-2">{{$menuItem->title }}</span>
        </a>
    @endforeach
</div>
