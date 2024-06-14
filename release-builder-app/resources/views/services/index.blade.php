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
                <div class="flex justify-between items-center mb-4">
                    <a href="/services/{{ $service->id }}" class="pack-link">
                        <i class="fa-regular fa-file-lines"></i> {{ $service->name }}
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="mt-6 mb-2 text-center">
            <p class="text-gray-600 italic">No services added</p>
        </div>
    @endforelse

@endsection
