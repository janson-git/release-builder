<div class="mt-12 flex justify-start">
    {{--                <a class="pure-menu-heading" href="/user">{{ $user?->getLogin() }}</a>--}}
    @foreach ( $mainMenu as $menuItem)
        <?php /** @var $menuItem \Service\Menu\MenuItem */ ?>
        <a class="mr-12 hover:underline {!! $menuItem->isSelected() ? 'font-bold' : '' !!}" href="{{$menuItem->route }}">
            @if ($menuItem->iconClass)
                <i class="{{ $menuItem->iconClass }} icon"></i>
            @else
                <i class="fa-solid icon"></i>
            @endif
            <span class="ml-2">{{$menuItem->title }}</span>
        </a>
    @endforeach
</div>
