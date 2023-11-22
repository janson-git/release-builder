<html lang="en">
<head>
    <title>{{ $code }} {{ $reason }}</title>
    <style>
        body {
            width: 800px;
            margin-left: auto;
            margin-right: auto;
            padding: 0;
            text-align: center;
            background-color: #edd;
            border-left: 1px solid #f66;
            border-right: 1px solid #f66;
            box-shadow: 0 5px 15px;
        }
        .header {
            background-color: #f66;
            padding: 30px;
        }
        .content {
            text-align: left;
            padding: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ $code }} {{ $reason }}</h2>
        <p>
            <b>{{ get_class($exception) }}</b> throwed
        </p>
    </div>
    <div class="content">
        <h3>{{ $exception->getMessage() }}</h3>
        <p>
            <b>in {{ $exception->getFile() }} on {{ $exception->getLine() }}</b>
        </p>
        <p>Stack trace:</p>
        <div style="font-family: monospace">
            {!! str_replace("\n", '<br>', $exception->getTraceAsString()) !!}
        </div>
    </div>
</body>
</html>
