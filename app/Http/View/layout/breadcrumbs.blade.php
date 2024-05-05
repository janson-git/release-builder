<?php /** @var $view \Admin\View */ ?>

<div class="mt-1 border-y-2 py-0.5 flex justify-start text-gray-small">
    @if ($view->hasBreadcrumbs())
        @foreach ($view->getBreadcrumbs() as $item)
            @if (!$loop->first)
                <span class="ml-2"> > </span>
            @endif

            <?php /** @var $item \Service\Breadcrumbs\Breadcrumb */ ?>
            <span class="ml-2">
                <?php $isActiveBreadcrumb = $item->url !== null && $item->url !== \request()->getUri()->getPath() ?>
                {!! $isActiveBreadcrumb ? "<a href=\"$item->url\" class=\"text-sky-600 hover:underline\">" : '<span>' !!}
                <span>{{ $item->title }}</span>
                {!! $isActiveBreadcrumb ? '</a>' : '<span>' !!}
            </span>
        @endforeach
    @else
        &nbsp;
    @endif

    <div class="pure-u">
        <div id="loader"></div>
    </div>
</div>
