<?php
/**
 * @var $serviceList \App\Models\Service[]
 */
?>
@extends('layout')

@section('pageActions')
    <x-main-page-action href="/services/add">Add service</x-main-page-action>
@endsection

@section('content')
    @forelse($serviceList as $service)
        <div class="card pack-card mt-6 border-t-2 border-blue-200">
            <div>
                <div class="flex mb-4 items-center">
                    @if($service->status === 'failed')
                        <i class="fa-solid fa-xmark-circle text-warning"></i>
                    @else
                        <i class="fa-solid fa-check-circle text-success"></i>
                    @endif

                    <a href="/services/{{ $service->id }}" class="ml-4 pack-link">
                        <i class="fa-regular fa-file-lines"></i> {{ $service->name }}
                    </a>
                </div>

                <div>
                    <h4>Branches</h4>
                @foreach($service->getBranches() as $branch)
                    <p>- {{ $branch }}</p>
                @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="mt-6 mb-2 text-center">
            <p class="text-gray-600 italic">No services added</p>
        </div>
    @endforelse

@endsection
