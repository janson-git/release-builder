<?php
/**
 * @var $releaseList \App\Models\Release[]
 */
?>
@extends('layout')

@section('pageActions')
    <x-main-page-action href="/releases/create">New release</x-main-page-action>
@endsection

@section('content')
    @forelse($releaseList as $release)
        <div class="card pack-card mt-6 border-t-2 border-blue-200">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <a href="/releases/{{ $release->id }}" class="pack-link">
                        <i class="fa-regular fa-file-lines"></i> {{ $release->name }}
                    </a>
                </div>
            </div>

            <h4>Services</h4>
            <ul class="my-2 pl-2">
                @if (!empty($release->services))
                    @foreach($release->services as $service)
                        <li>{{ $service->repository_url }}</li>
                        <!-- TODO: display release branch name and link to PR if exists -->
                    @endforeach
                @else
                    <li class="empty"><i>No services selected</i></li>
                @endif
            </ul>

            <h4>Branches</h4>
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
    @empty
        <div class="mt-6 mb-2 text-center">
            <p class="text-gray-600 italic">No releases added</p>
        </div>
    @endforelse
@endsection
