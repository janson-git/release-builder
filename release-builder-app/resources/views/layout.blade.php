<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Release Builder</title>

    <link href="/fontawesome/css/fontawesome.css" rel="stylesheet">
    <link href="/fontawesome/css/regular.css" rel="stylesheet">
    <link href="/fontawesome/css/solid.css" rel="stylesheet">

    <!-- FIXME: use local tailwind file for development -->
    <script src="/tailwindcss-3.4.5.js"></script>

    <link rel="stylesheet" href="/css/new.css">
    <link rel="stylesheet" href="/css/custom-buttons.css">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <script src="/js/jquery-2.1.1.min.js"></script>
    <script src="/js/ui.js"></script>
    <script src="/js/clipboard.js"></script>
    <script src="/js/BranchesFilter.js"></script>
</head>

<body class="bg-gray-50 mb-6">

    <div class="w-full pt-4 border-b-2">
        <div class="lg:w-1/2 lg:px-0 lg:min-w-[800px] px-4 w-full mx-auto">
            <div id="loader-wrapper">
                <div id="loader-holder">
                    <div id="loader"></div>
                </div>
            </div>

            @include('layout.navigation')
        </div>
    </div>

    <div class="w-full mt-6">
        <div class="lg:w-1/2 lg:px-0 lg:min-w-[800px] px-4 w-full mx-auto">
            @include('layout.heading')

            <div class="mt-8">
                @yield('content')

                {{-- todo fix --}}
                @if (isset($_logs))
                    <button id="logs-toggle-button" class="mb-4 mt-6 btn btn-muted-outline">
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
        </div>
    </div>
</body>
</html>
