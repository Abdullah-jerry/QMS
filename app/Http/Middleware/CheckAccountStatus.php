<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        if ($user->isLocked()) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account is temporarily locked. Please try again later.']);
        }

        return $next($request);
    }
}
