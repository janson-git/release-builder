<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSandboxRequest;
use App\Models\Sandbox;
use App\Models\User;
use App\Services\GitRepositoryService;
use Illuminate\Http\Response;

class SandboxController extends Controller
{
    public function show(int $id): Response
    {
        /** @var User $user */
        $user = app()->auth->getUser();

        $sandbox = Sandbox::find($id);

        $gitRepoService = app(GitRepositoryService::class);

        $wrappedService = collect([$sandbox->service]);

        $branchesDiffs = $gitRepoService->getToMasterStatus(
            $sandbox->branches,
            $wrappedService
        );


        return response()->view('sandboxes/show', [
            'header' => 'Sandbox',
            'sandbox' => $sandbox,
            'branchesDiffs' => $branchesDiffs,
        ]);
    }

    public function edit(int $id): Response
    {
        $sandbox = Sandbox::find($id);
        $release = $sandbox->release;

        $gitRepoService = app(GitRepositoryService::class);

        $wrappedService = collect([$sandbox->service]);

        $branches = $gitRepoService->getBranchesWithServices($wrappedService);
        $branchesDiffs = $gitRepoService->getToMasterStatus(
            $sandbox->branches,
            $wrappedService
        );


        return response()->view('sandboxes/edit', [
            'header' => 'Edit Sandbox',
            'sandbox' => $sandbox,
            'branches' => $branches,
            'branchesDiffs' => $branchesDiffs,
            'release' => $release,
            'selected' => [],
        ]);
    }

    public function update(int $id, UpdateSandboxRequest $request)
    {
        $sandbox = Sandbox::findOrFail($id);
        $release = $sandbox->release;

        // TODO: Is there needed to store sandbox branches separately
        // TODO:   from release branches? And where?
        // TODO: If yes - need update Release Action where branches merging
        // TODO:   for all sandboxes.


        // filter incoming branches from common release branches
        $onlyServiceBranches = array_diff(
            $request->getBranches(),
            $release->branches->getCommonBranches()
        );

        $release->branches->setServiceBranches(
            $onlyServiceBranches,
            $sandbox->service_id
        );
        $release->save();


        return redirect("/sandboxes/{$sandbox->id}");
    }
}
