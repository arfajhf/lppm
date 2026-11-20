<?php

namespace App\Support;

use Illuminate\Http\Request;

class LocalAuthentication
{
    public static function available(Request $request): bool
    {
        return app()->environment('local')
            && config('local_auth.enabled')
            && in_array($request->ip(), ['127.0.0.1', '::1'], true);
    }
}
