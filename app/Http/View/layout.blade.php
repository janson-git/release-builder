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

    <script src="https://cdn.tailwindcss.com"></script>

{{--    <link rel="stylesheet" href="/css/pure-min.css">--}}
{{--    <link rel="stylesheet" href="/css/side-menu.css">--}}
{{--    <link rel="stylesheet" href="/css/girds-min.css">--}}
{{--    <link rel="stylesheet" href="/css/custom.css">--}}
    <link rel="stylesheet" href="/css/new.css">
{{--    <link rel="stylesheet" href="/css/custom-buttons.css">--}}
    <link rel="icon" type="image/png" sizes="32x32" href="/rocket_32.png">
    <script src="/js/jquery-2.1.1.min.js"></script>
</head>

<body class="bg-gray-50 flex justify-around">
<div class="w-1/2">
    @include('layout.navigation', ['mainMenu' => $mainMenu])

{{--        @include('layout.breadcrumbs', ['view' => $view])--}}

    <div class="mt-6">
        @include('layout.heading', ['header' => $header, 'title' => $title, 'action' => $action ?? null])
    </div>

    <div class="mt-4">
        @yield('content')

{{--        --}}{{-- todo move it somewhere--}}
{{--        @if (isset($_logs))--}}
{{--            <button id="logs-toggle-button">--}}
{{--                Show Debug Logs--}}
{{--            </button>--}}
{{--            <div class="pure-g logs-cont" id="logs-container">--}}
{{--                @foreach ($_logs as $info)--}}
{{--                    <div class="pure-u-1">--}}
{{--                        <div style="word-break: break-all; padding: 0.3em">--}}
{{--                            {{ $info }}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                @endforeach--}}
{{--            </div>--}}
{{--        @endif--}}

    </div>
</div>

<script src="/js/ui.js"></script>
</body>
</html>
