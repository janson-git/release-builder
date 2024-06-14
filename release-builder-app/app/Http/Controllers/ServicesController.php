<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AddServiceRequest;
use App\Services\GitService;
use App\Services\ServicesService;

class ServicesController extends Controller
{
    public function index()
    {
        $serviceService = app(ServicesService::class);

        return response()->view('services.index', [
            'header' => 'Services',
            'serviceList' => $serviceService->getServices(),
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
        $repoPath = preg_replace('#[^a-zA-Z0-9:@./\-]#', '', $repoPath);

        $repoNameFull = mb_substr($repoPath, strrpos($repoPath, '/') + 1);
        $dirName = str_replace('.git', '', $repoNameFull);
//
//        if (str_starts_with($repoPath, 'git@github') && !$this->app->getAuth()->isSshKeyExists()) {
//            return $this->app->json([
//                'error' => 'You should add SSH key in your profile to use SSH repository links',
//            ],
//                StatusCode::HTTP_UNPROCESSABLE_ENTITY
//            );
//        }

        app(GitService::class)->cloneRepository($repoPath, $dirName);

        dd($request);
    }
}
