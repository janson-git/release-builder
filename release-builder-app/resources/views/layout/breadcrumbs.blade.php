<div class="ml-3 mt-1 py-1 flex justify-start text-gray-small items-center">
    @if (!empty($breadcrumbs))
        <span class="ml-2"><i class="fa fa-home"></i></span>

        @foreach ($breadcrumbs->items() as $item)
            <span class="ml-2 fa fa-chevron-right text-gray-300"></span>

                <?php /** @var $item \App\View\Breadcrumb */ ?>
            <span class="ml-2">
                <?php $isActiveBreadcrumb = $item->url !== null && $item->url !== \Route::current()->getName() ?>
                {!! $isActiveBreadcrumb ? "<a href=\"$item->url\" class=\"text-sky-600 hover:underline\">" : '<span>' !!}
                <span>{{ $item->title }}</span>
                {!! $isActiveBreadcrumb ? '</a>' : '<span>' !!}
            </span>
        @endforeach
    @else
        &nbsp;
    @endif
</div>
