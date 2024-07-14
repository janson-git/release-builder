<?php
/**
 * @var $release \App\Models\Release
 */
?>
@extends('layout')

@section('pageActions')
    <x-secondary-page-action href="/releases/{{ $release->id }}">Back to release</x-secondary-page-action>
@endsection

@section('content')
    <span class="mr-4 btn btn-muted-outline font-bold">STATUS BADGE</span>

    <div class="card border-t-2 border-gray-200">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <span class="font-bold text-xl mr-2">Build</span>
                <span class="p-1 bg-sky-100 text-blue-800">PLACEHOLDER</span>
                <i class="ml-1 fa-regular fa-copy text-gray-800 cursor-pointer" onclick="Clipboard.writeToClipboard('PLACEHOLDER')"></i>

                <span class="ml-4 px-2 py-1 text-xs border bg-green-200 text-gray-600 rounded">
                    active
                </span>
            </div>

            <div class="build-relative-date">
                {{ $release->delivery_date}}
            </div>
        </div>
    </div>

    <div class="card">
        @foreach ($actionLog as $sectionId => $sectionLogs)
            <div class="mb-4">
                <h2>Command: {{ $sectionLogs['command'] ?? $sectionId }}</h2>
                <div>
                    @foreach ($sectionLogs as $key => $result)
                        <div class="mt-2 font-bold">{{ $key }}</div>
                        <div class="mt-2 pl-4 overflow-x-auto text-gray-600">
                            <pre>{!! parseActionLog($result) !!}</pre>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

{{--        @if ($exceptionsBySection = $runtime->getExceptions())--}}
{{--            @foreach ($exceptionsBySection as $sectionId => $exceptions)--}}
{{--                <div class="text-red-800">--}}
{{--                    <h1>Exceptions at {{ $runtime->getSectionName($sectionId) }}:</h1>--}}
{{--                    @foreach ($exceptions as $exception)--}}
{{--                            <?php /* @var $exception \Exception */ ?>--}}
{{--                        <div class="separator">--}}
{{--                            <div>{{ $exception->getMessage() }}</div>--}}
{{--                            <b>File:</b> {{ $exception->getFile() }}:{{ $exception->getLine() }}<br>--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--            @endforeach--}}
{{--        @endif--}}

{{--        @if ($errorsBySection = $runtime->getErrors())--}}
{{--            @foreach ($errorsBySection as $sectionId => $errors)--}}
{{--                <div class="text-red-800">--}}
{{--                    <h1>Errors in {{ $runtime->getSectionName($sectionId) }}:</h1>--}}
{{--                    @foreach ($errors as $error)--}}
{{--                        <div class="mt-1">--}}
{{--                            {{ \Admin\View::parse($error) }}--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--            @endforeach--}}
{{--        @endif--}}
    </div>

@endsection
