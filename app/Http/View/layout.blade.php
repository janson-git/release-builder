<?php
/** @var \Admin\View $view */
/** @var \Service\User $user */
/** @var \Slim\Http\Request $request */
$currentPath = \request()->getUri()->getPath();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $_identify }} Config Server</title>

    <link href="/fontawesome/css/fontawesome.css" rel="stylesheet">
    <link href="/fontawesome/css/regular.css" rel="stylesheet">
    <link href="/fontawesome/css/solid.css" rel="stylesheet">

    <link rel="stylesheet" href="/css/pure-min.css">
    <link rel="stylesheet" href="/css/side-menu.css">
    <link rel="stylesheet" href="/css/girds-min.css">
    <link rel="stylesheet" href="/css/custom.css">
    <link rel="stylesheet" href="/css/custom-buttons.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/rocket_32.png">
    <script src="/js/jquery-2.1.1.min.js"></script>
</head>

<body>
<div id="layout">
    <!-- Menu toggle -->
    <a href="#menu" id="menuLink" class="menu-link">
        <!-- Hamburger icon -->
        <span></span>
    </a>
    <div id="menu">
        <div class="pure-menu pure-menu-open">
            <a class="pure-menu-heading" href="/user">{{ $user?->getLogin() }}</a>
            <ul>
                @foreach ( $mainMenu as $menuItem)
                <?php /** @var $menuItem \Service\Menu\MenuItem */ ?>
                <li {!! $menuItem->isSelected() ? 'class="pure-menu-selected"' : '' !!}>
                    <a href="{{$menuItem->route }}">
                        @if ($menuItem->iconClass)
                            <i class="{{ $menuItem->iconClass }} icon"></i>
                        @else
                            <i class="fa-solid icon"></i>
                        @endif
                        <span>{{$menuItem->title }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div id="main">

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

        @if ( $header ||  $title)
        <div class="header">
            @if ( $header)
                <h1>{!! $header !!}</h1>
            @endif
            @if ( $title )
                <h2>{!! $title !!}</h2>
            @endif
        </div>
        @else
            <br/>
        @endif

        <div class="content">

            @yield('content')

            @if (isset($_logs))
                <button id="logs-toggle-button">
                    Show Debug Logs
                </button>
                <div class="pure-g logs-cont" id="logs-container">
                    @foreach ($_logs as $info)
                        <div class="pure-u-1">
                            <div style="word-break: break-all; padding: 0.3em">
                                {{ $info }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>

<script src="/js/ui.js"></script>
</body>
</html>
