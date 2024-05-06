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

    <link rel="stylesheet" href="/css/new.css">
    <link rel="stylesheet" href="/css/custom-buttons.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/rocket_32.png">
    <script src="/js/jquery-2.1.1.min.js"></script>
</head>

<body class="bg-gray-50 w-full mx-auto flex justify-center">
<div class="w-1/2 min-w-[800px] justify-around">
    @include('layout.navigation', ['mainMenu' => $mainMenu])
    @include('layout.breadcrumbs', ['view' => $view])

    <div class="mt-6">
        @include('layout.heading', ['header' => $header, 'title' => $title, 'action' => $action ?? null])
    </div>

    <div class="mt-8">
        @yield('content')

        {{-- todo fix --}}
        @if (isset($_logs))
            <button id="logs-toggle-button" class="inline-block mb-4 text-gray-400 border border-gray-400 hover:bg-gray-400 hover:text-white px-2 py-1 rounded">
                Show Debug Logs
            </button>
            <div class="mb-4 logs-cont" id="logs-container">
                @foreach ($_logs as $info)
                    <div style="word-break: break-all; padding: 0.3em">
                        {{ $info }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div id="loader" class="hidden fixed bottom-10" style="left: 70%;">
        <div class="mx-auto">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </div>
    </div>
</div>

<script src="/js/ui.js"></script>
</body>
</html>
