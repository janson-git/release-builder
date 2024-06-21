<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AddServiceRequest;
use App\Models\Service;
use App\Services\GitRepositoriesService;

class ServicesController extends Controller
{
    public function index()
    {
        return response()->view('services.index', [
            'header' => 'Services',
            'serviceList' => Service::all(),
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

        // Check service already exists
        $service = Service::where('repository_url', $repoPath)->first();
        if ($service) {
            return back()->withErrors([
                'repository_url' => 'This repository URL already exists'
            ]);
        }

//        if (str_starts_with($repoPath, 'git@github') && !$this->app->getAuth()->isSshKeyExists()) {
//            return $this->app->json([
//                'error' => 'You should add SSH key in your profile to use SSH repository links',
//            ],
//                StatusCode::HTTP_UNPROCESSABLE_ENTITY
//            );
//        }

        $service = Service::create([
            'name' => $repoPath,
            'repository_url' => $repoPath,
        ]);

        try {
            app(GitRepositoriesService::class)->cloneRepository($repoPath);
            $service->update(['status' => Service::STATUS_CLONED]);
        } catch (\Throwable $e) {
            $service->update(['status' => Service::STATUS_FAILED]);

            return back()->withErrors([
                'exception' => $e->getMessage(),
            ]);
        }

        return redirect()->route('services');
    }
}
