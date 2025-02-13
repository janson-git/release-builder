<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AddServiceRequest;
use App\Models\Release;
use App\Models\Service;
use App\Models\Setting;
use App\Services\GitRepositoryService;
use App\Services\SandboxRepositoryService;

class ServicesController extends Controller
{
    public function index()
    {
        $isHttpsEnabled = Setting::getValueByName('is_https_enabled');
        if (!$isHttpsEnabled) {
            $services = Service::notHttps()->get();
        } else {
            $services = Service::all();
        }

        return response()->view('services.index', [
            'header' => 'Services',
            'subheader' => "{$services->count()} services on this page",
            'serviceList' => $services,
            'firstService' => Service::count() === 0,
            'firstRelease' => Release::count() === 0,
        ]);
    }

    public function create()
    {
        return response()->view('services.create', [
            'header' => 'Services',
        ]);
    }

    public function store(AddServiceRequest $request)
    {
        // SSH link like: git@github.com:janson-git/release-builder.git
        // HTTPS url like: https://github.com/janson-git/release-builder.git

        $repoPath = $request->getRepositoryPath();
        if (str_starts_with($repoPath, 'git@github') && !auth()->user()->hasSshKey()) {
            return back()->withErrors([
                'repository_url' => 'You should add SSH key in your profile to use SSH repository links',
            ]);
        }

        $isHttpsEnabled = Setting::getValueByName('is_https_enabled');
        if (!$isHttpsEnabled && str_starts_with($repoPath, 'https')) {
            return back()->withErrors([
                'repositiry_url' => 'It is not able to use HTTPS repository URL! Use SSH repository links instead',
            ]);
        }

        // Check service already exists
        $service = Service::where('repository_url', $repoPath)->first();
        if ($service && $service->status === Service::STATUS_CLONED) {
            return back()->withErrors([
                'repository_url' => 'This repository URL already exists'
            ]);
        }

        if (!$service) {
            $service = Service::create([
                'directory' => app(GitRepositoryService::class)->getRepositoryDirNameFromUrl($repoPath),
                'repository_name' => str_replace(['git@github.com:', 'https://github.com/'], '', $repoPath),
                'repository_url' => $repoPath,
            ]);
        }

        try {
            app(GitRepositoryService::class)->cloneRepository($service);

            $service->update(['status' => Service::STATUS_CLONED]);

            // Also create sandbox for new service
            // TODO: research for quick local clone without remote here
            $sandboxRepoService = app(SandboxRepositoryService::class);
            $sandboxRepoService->cloneRepository($service);
        } catch (\Throwable $e) {
            $service->update(['status' => Service::STATUS_FAILED]);

            throw $e;
        }

        return redirect()->route('services');
    }

    public function retryCloneRepository(int $serviceId)
    {
        $service = Service::find($serviceId);

        try {
            app(GitRepositoryService::class)->cloneRepository($service);
            $service->update(['status' => Service::STATUS_CLONED]);
        } catch (\Throwable $e) {
            $service->update(['status' => Service::STATUS_FAILED]);

            throw $e;
        }

        return redirect()->route('services');
    }

    public function fetchRepository(string $serviceId)
    {
        /** @var Service $service */
        $service = Service::find($serviceId);
        $serviceRepo = app(GitRepositoryService::class)->getServiceRepository($service);

        $serviceRepo->fetch();

        return redirect()->route('services');
    }
}
