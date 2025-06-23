<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetUserBranch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {

        if ($request->hasHeader('X-Branch-ID')) {
            try {
                $branchId = Crypt::decryptString($request->header('X-Branch-ID'));

                Log::info('SetUserBranch Middleware Triggered', [
                    'branch_id' => $branchId,
                ]);

                app()->instance('currentBranchId', $branchId);
            } catch (\Exception $e) {
                Log::error('Invalid X-Branch-ID header', [
                    'error' => $e->getMessage(),
                    'header' => $request->header('X-Branch-ID'),
                ]);

                return response()->json(['error' => 'Invalid Branch ID'], 400);
            }
        }
        // else {
        //      return response()->json(['error' => 'Unauthorized'], 403);
        // }

        return $next($request);
    }
}
