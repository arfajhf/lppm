<?php

namespace App\Http\Controllers;

use App\Support\LppmAccess;
use App\Support\LppmWorkspace;

/**
 * Menyajikan ringkasan akses LPPM berdasarkan role dan permission dari SSO.
 */
class AccessController extends Controller
{
    /**
     * Menampilkan status akses pengguna, termasuk peran yang dibutuhkan dan
     * permission yang masih belum terpenuhi.
     */
    public function index()
    {
        $user = auth()->user();
        $module = LppmWorkspace::module($user, 'access');

        $requiredRoles = LppmAccess::requiredRoles();
        $requiredPermissions = LppmAccess::requiredPermissions();
        $currentSso = LppmAccess::currentSsoAccessSnapshot();
        $missingRoles = LppmAccess::missingSsoRoles();
        $missingPermissions = LppmAccess::missingSsoPermissions();
        $effectivePermissions = $user->getEffectiveLppmPermissions();

        return view('access.index', compact(
            'user',
            'module',
            'requiredRoles',
            'requiredPermissions',
            'currentSso',
            'missingRoles',
            'missingPermissions',
            'effectivePermissions'
        ));
    }
}
