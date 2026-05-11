<?php

namespace App\Http\Controllers;

use App\Services\SSOConfigService;
use App\Services\SSOService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Menangani alur autentikasi masuk melalui SSO UMB.
 */
class SSOAuthController extends Controller
{
    public function __construct(
        private readonly SSOService $ssoService
    ) {
    }

    /**
     * Mengarahkan pengguna ke endpoint otorisasi SSO dengan parameter klien aktif.
     */
    public function redirectToSSO()
    {
        $query = http_build_query([
            'client_id' => SSOConfigService::getClientId(),
            'redirect_uri' => SSOConfigService::getRedirect(),
            'response_type' => 'code',
            'scope' => 'user.profile',
        ]);

        return redirect(rtrim((string) SSOConfigService::getUrl(), '/') . "/sso/redirect?{$query}");
    }

    /**
     * Menukar authorization code, menyinkronkan akun lokal, lalu membuat sesi login.
     */
    public function handleCallback(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $ssoData = $this->ssoService->exchangeCodeForToken(
            $request->string('code')->toString(),
            (string) SSOConfigService::getRedirect()
        );

        if (! $ssoData || ! is_array($ssoData['user'] ?? null)) {
            return redirect()->route('login')->with('error', 'SSO gagal. Silakan coba lagi.');
        }

        $user = $this->ssoService->syncUserFromSSO($ssoData['user']);

        Auth::login($user);
        $request->session()->regenerate();
        session(['sso_token' => $ssoData['access_token'] ?? null]);

        return redirect()->intended(route('dashboard'));
    }
}
