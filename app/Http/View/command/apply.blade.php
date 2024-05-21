<?php
/**
 * @var $runtime \Commands\CommandRuntime
 * @var $context \Commands\CommandContext
 * @var $packId
 */
?>

@extends('./layout.blade.php')

@section('content')
<div class="card">
    @foreach ($runtime->getLogs() as $sectionId => $sectionLogs)
        <div>
            <h2>Command: {{ $runtime->getSectionName($sectionId) }}</h2>
            <div>
                @foreach ($sectionLogs as $key => $result)
                    <div class="mt-2 font-bold">{{ $key }}</div>
                    <div class="mt-2 pl-4 overflow-x-scroll">
                        <pre>{!! \Admin\View::parse($result) !!}</pre>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if ($exceptionsBySection = $runtime->getExceptions())
        @foreach ($exceptionsBySection as $sectionId => $exceptions)
            <div class="text-red-800">
                <h1>Exceptions at {{ $runtime->getSectionName($sectionId) }}:</h1>
                @foreach ($exceptions as $exception)
                    <?php /* @var $exception \Exception */ ?>
                    <div class="separator">
                        <div>{{ $exception->getMessage() }}</div>
                        <b>File:</b> {{ $exception->getFile() }}:{{ $exception->getLine() }}<br>
                    </div>
                @endforeach
            </div>
    @endforeach
    @endif

    @if ($errorsBySection = $runtime->getErrors())
        @foreach ($errorsBySection as $sectionId => $errors)
            <div class="text-red-800">
                <h1>Errors in {{ $runtime->getSectionName($sectionId) }}:</h1>
                @foreach ($errors as $error)
                    <div class="mt-1">
                        {{ \Admin\View::parse($error) }}
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif
</div>
@endsection
