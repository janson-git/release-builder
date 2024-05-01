<div class="breadcrumbs pure-g">
    @if ( $view->hasBreadcrumbs() )
        <div class="pure-u-4-5">
            <ul>
                @foreach ($view->getBreadcrumbs() as $item)
                        <?php /** @var $item \Service\Breadcrumbs\Breadcrumb */ ?>
                    <li>
                        <?php $isActiveBreadcrumb = $item->url !== null && $item->url !== \request()->getUri()->getPath() ?>
                        {!! $isActiveBreadcrumb ? "<a href=\"$item->url\">" : '<span>' !!}
                        @if ($item->iconClass)
                            <i class="{{ $item->iconClass }} icon"></i>
                        @endif
                        <p>{{ $item->title }}</p>
                        {!! $isActiveBreadcrumb ? '</a>' : '<span>' !!}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="pure-u">
        <div id="loader"></div>
    </div>
</div>
<div class="breadcrumbs-placeholder pure-u-1"></div>
