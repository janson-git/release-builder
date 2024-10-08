<?php

namespace App\Http\Middleware;

use App\Models\Service;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitialTourRouting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        if (!auth()->user()) {
            return $next($request);
        }

        // if user not uploaded ssh key
        if (!auth()->user()->hasSshKey()) {
            if (!$request->is('user', 'user/add-key')) {
                return redirect('/user/add-key');
            }
            return $next($request);
        }

        // if no services added
        if (Service::count() === 0) {
            if ($request->is('services', 'services/add', 'user', 'user/add-key')) {
                return $next($request);
            }
            return redirect()->route('services');
        }

        return $next($request);
    }
}
