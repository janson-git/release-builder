<?php
/**
 * @var $releaseList \App\Models\Release[]
 */
?>
@extends('layout')

@section('content')
    <h1>RELEASES</h1>

    @foreach($releaseList as $release)
        <div class="card pack-card mt-6 border-t-2 border-blue-200">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <a href="/releases/{{ $release->id }}" class="pack-link">
                        <i class="fa-regular fa-file-lines"></i> {{ $release->name }}
                    </a>
                </div>
            </div>

            <div class="">Branches</div>
            <ul class="mt-2 p-2 border border-gray-400 overflow-scroll">
                @if (!empty($release->branches))
                    @foreach($release->branches as $branch)
                        <li>{{ $branch }}</li>
                    @endforeach
                @else
                    <li class="empty"><i>No branches added</i></li>
                @endif
            </ul>
        </div>
    @endforeach
@endsection
