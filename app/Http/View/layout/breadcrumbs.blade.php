<?php /** @var $view \Admin\View */ ?>

<div class="mt-1 border-t py-1 flex justify-start text-gray-small items-center">
    @if ($view->hasBreadcrumbs())
        <span class="ml-2"><i class="fa fa-home"></i></span>

        @foreach ($view->getBreadcrumbs() as $item)
            <span class="ml-2 fa fa-chevron-right text-gray-300"></span>

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

    <div id="loader"></div>
</div>
