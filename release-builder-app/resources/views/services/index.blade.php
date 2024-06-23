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
        <div class="card mb-6">
            <div class="flex justify-start items-center">
                <p class="font-bold text-lg">{{ $service->directory }}</p>
            </div>

            <div class="text-sm">
                <i class="text-xs fa-solid fa-external-link"></i>
                <a href="{{ $service->repository_url }}" target="_blank" class="ml-1 mr-7 text-blue-400 hover:text-blue-600 hover:underline">
                    {{ $service->repository_url }}
                </a>
            </div>

            @php($time = $service->repository->getFilesUpdateTime())
            <div class="text-sm">
                <i class="text-xs fa-solid fa-refresh"></i>
                <span class="ml-1">
                    {{ $time['back'] }} {{ $time['date'] }}
                </span>
            </div>

            <pre class="mt-6 overflow-scroll text-xs font-mono py-4 pl-2 bg-gray-100 border border-gray-600">{!! implode("\n", $service->repository->getLastCommitsLog() ?? []) !!}</pre>

            <div class="mt-6">
                <div class="flex justify-start">
                    <select class="border-b border-black focus:outline-none">
                        @foreach ($service->repository->getAllBranches() as $branch)
                            <option {{ str_starts_with($branch, '*') ? 'selected' : '' }} value="{{ trim($branch, '* ') }}"
                                    title="{{ htmlentities($branch) }}"
                            >{{ substr($branch,0, 40) }}</option>
                        @endforeach
                    </select>
                    <button class="ml-4 bg-gray-100 border border-gray-200 hover:bg-gray-200 text-xs px-4 py-1 rounded" onclick='admin.checkout("{{ $service->name }}", this, $(this).parent().find("select").val())'>
                        checkout branch
                    </button>
                </div>
            </div>

            <div class="mt-4"></div>
            @foreach ($service->repository->getRemotes() as $remote)
                <div class="text-sm">
                    <i class="ml-1 text-xs fa-solid fa-info"></i>
                    <span class="ml-2">{{ $remote }}</span>
                </div>
            @endforeach

            <div class="mt-6 flex justify-start">
                <button class="px-4 py-1 btn btn-success" onclick='admin.update("{{ $service->name }}", this)'>update</button>
                <button class="ml-4 px-4 py-1 btn btn-muted" onclick='admin.fixGit("{{ $service->name }}", this)'>reset branch</button>
                <button class="ml-4 px-2 py-1 btn btn-danger" onclick='admin.fixGit("{{ $service->name }}", this, 1)'>reset and delete files</button>
            </div>
        </div>
    @empty
        <div class="mt-6 mb-2 text-center">
            <p class="text-gray-600 italic">No services added</p>
        </div>
    @endforelse

@endsection
