<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class SSOConfigService
{
    public static function getConfig(string $key, $default = null)
    {
        return Config::get('services.sso.' . $key, $default);
    }

    public static function getUrl(): ?string
    {
        return self::getConfig('url');
    }

    public static function getClientId(): ?string
    {
        return self::getConfig('client_id');
    }

    public static function getClientSecret(): ?string
    {
        return self::getConfig('client_secret');
    }

    public static function getRedirect(): ?string
    {
        return self::getConfig('redirect');
    }
}
