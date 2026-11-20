<?php

namespace App\Http\Middleware;

use App\Support\LocalAuthentication;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictLocalAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->get('authentication_method') === 'local' && ! LocalAuthentication::available($request)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        return $next($request);
    }
}
