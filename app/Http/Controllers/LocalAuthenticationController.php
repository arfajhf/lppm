<?php

namespace App\Http\Controllers;

use App\Support\LocalAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LocalAuthenticationController extends Controller
{
    public function store(Request $request)
    {
        abort_unless(LocalAuthentication::available($request), 404);
        $data = $request->validate(['email' => 'required|string|email|max:255', 'password' => 'required|string|max:1024']);
        $credentials = ['email' => strtolower(trim($data['email'])), 'password' => $data['password']];
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'Email atau password tidak sesuai.']);
        }
        if (! $request->user()->hasLppmPermission('lppm.access')) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Email atau password tidak sesuai.']);
        }
        $request->session()->regenerate();
        $request->session()->forget('sso_token');
        $request->session()->put('authentication_method', 'local');

        return redirect()->intended(route('dashboard'));
    }
}
