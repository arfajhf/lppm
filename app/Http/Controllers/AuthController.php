<?php

namespace App\Http\Controllers;

use App\Services\SSOConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Mengelola tampilan login lokal dan alur keluar menuju SSO terpusat.
 */
class AuthController extends Controller
{
    /**
     * Menyusun tautan login pendamping seperti daftar akun dan lupa kata sandi.
     */
    public function create()
    {
        $clientLoginUrl = route('login');
        $ssoBaseUrl = rtrim((string) SSOConfigService::getUrl(), '/');

        $registerUrl = $ssoBaseUrl.'/register?'.http_build_query([
            'client_id' => SSOConfigService::getClientId(),
            'redirect_uri' => SSOConfigService::getRedirect(),
            'client' => $clientLoginUrl,
        ]);

        $forgotPasswordUrl = $ssoBaseUrl.'/forgot-password?'.http_build_query([
            'client_id' => SSOConfigService::getClientId(),
            'redirect_uri' => SSOConfigService::getRedirect(),
            'client' => $clientLoginUrl,
        ]);

        return view('auth.login', compact('registerUrl', 'forgotPasswordUrl'));
    }

    /**
     * Mengakhiri sesi aplikasi lalu meneruskan proses logout ke server SSO.
     */
    public function destroy(Request $request)
    {
        $local = $request->session()->get('authentication_method') === 'local';
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($local) {
            return redirect()->route('login');
        }

        return redirect(
            rtrim((string) config('services.sso.url'), '/').'/logout?redirect='.urlencode(route('login'))
        );
    }
}
