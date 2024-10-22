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
    <div class="card border-t-2 border-gray-200">
        <div class="mt-50 mb-50">
<textarea class="w-full mb-2 border border-gray-400 p-1 overflow-x-scroll text-nowrap" rows="20">
{{ $releaseInfo }}
</textarea>
        </div>

@endsection
