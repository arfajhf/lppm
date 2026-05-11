<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SSOService
{
    private const ADMIN_ROLE_NAMES = [
        'admin',
        'superadmin',
        'super_admin',
        'admin_lppm',
        'lppm_admin',
    ];

    public function exchangeCodeForToken(string $code, string $redirectUri): ?array
    {
        try {
            $response = Http::post(rtrim((string) SSOConfigService::getUrl(), '/') . '/api/sso/token', [
                'client_id' => SSOConfigService::getClientId(),
                'client_secret' => SSOConfigService::getClientSecret(),
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if (! $response->ok()) {
                Log::error('SSO token exchange failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                return null;
            }

            $payload['user'] = $this->mergeUserProfile(
                is_array($payload['user'] ?? null) ? $payload['user'] : [],
                is_string($payload['access_token'] ?? null) ? $payload['access_token'] : null
            );

            return $payload;
        } catch (\Throwable $exception) {
            Log::error('SSO token exchange error', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function syncUserFromSSO(array $ssoUser): User
    {
        $normalizedUser = $this->normalizeUserPayload($ssoUser);
        $user = null;

        if (! empty($normalizedUser['user_id'])) {
            $user = User::where('sso_user_id', $normalizedUser['user_id'])->first();
        }

        if (! $user && ! empty($normalizedUser['email'])) {
            $user = User::where('email', $normalizedUser['email'])->first();
        }

        if (! $user) {
            $user = new User();
        }

        $user->fill([
            'sso_user_id' => $normalizedUser['user_id'] ?? $user->sso_user_id,
            'username' => $normalizedUser['username'] ?? $user->username,
            'full_name' => $normalizedUser['full_name'] ?? $user->full_name,
            'name' => $normalizedUser['name'] ?? $normalizedUser['full_name'] ?? $normalizedUser['username'] ?? $normalizedUser['email'] ?? $user->name,
            'email' => $normalizedUser['email'] ?? $user->email,
            'role' => $this->resolvePrimaryRoleFromSSO($normalizedUser) ?? $user->role ?? 'dosen',
            'avatar' => $normalizedUser['avatar'] ?? $user->avatar,
            'identity_number' => $normalizedUser['identity_number'] ?? $user->identity_number,
            'faculty' => $normalizedUser['faculty'] ?? $user->faculty,
            'study_program' => $normalizedUser['study_program'] ?? $user->study_program,
            'password' => $user->password ?: bcrypt('dummy'),
            'is_admin' => $this->isAdminFromSSO($normalizedUser),
            'sso_roles' => $this->extractRoleNamesFromSSO($normalizedUser),
            'sso_permissions' => $this->extractPermissionNamesFromSSO($normalizedUser),
        ]);

        $user->save();

        return $user;
    }

    public function isAdminFromSSO(array $ssoUser): bool
    {
        if (isset($ssoUser['is_admin'])) {
            return (bool) $ssoUser['is_admin'];
        }

        foreach ($this->extractRoleNamesFromSSO($ssoUser) as $roleName) {
            if (in_array($roleName, self::ADMIN_ROLE_NAMES, true)) {
                return true;
            }
        }

        return false;
    }

    public function resolvePrimaryRoleFromSSO(array $ssoUser): ?string
    {
        return $this->extractRoleNamesFromSSO($ssoUser)[0] ?? null;
    }

    private function mergeUserProfile(array $user, ?string $accessToken): array
    {
        if ($accessToken === null || $accessToken === '') {
            return $this->normalizeUserPayload($user);
        }

        $candidates = [
            '/api/sso/me',
            '/api/sso/profile',
            '/api/user',
            '/api/me',
        ];

        foreach ($candidates as $path) {
            try {
                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->get(rtrim((string) SSOConfigService::getUrl(), '/') . $path);

                if (! $response->ok()) {
                    continue;
                }

                $profile = $response->json();

                if (! is_array($profile)) {
                    continue;
                }

                $profileUser = is_array($profile['user'] ?? null) ? $profile['user'] : $profile;

                if (is_array($profile['user_master'] ?? null)) {
                    $profileUser['user_master'] = $profile['user_master'];
                }

                return $this->normalizeUserPayload(array_replace_recursive($user, $profileUser));
            } catch (\Throwable $exception) {
                Log::debug('SSO profile enrichment failed', [
                    'path' => $path,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $this->normalizeUserPayload($user);
    }

    private function normalizeUserPayload(array $ssoUser): array
    {
        $userMaster = is_array($ssoUser['user_master'] ?? null)
            ? $ssoUser['user_master']
            : (is_array($ssoUser['userMaster'] ?? null) ? $ssoUser['userMaster'] : []);

        $username = $this->firstNonEmpty(
            $ssoUser['username'] ?? null,
            $ssoUser['user_name'] ?? null,
            $ssoUser['login'] ?? null,
            $userMaster['username'] ?? null
        );

        $fullName = $this->firstNonEmpty(
            $ssoUser['full_name'] ?? null,
            $ssoUser['fullname'] ?? null,
            $userMaster['full_name'] ?? null,
            $userMaster['fullname'] ?? null,
            $ssoUser['name'] ?? null
        );

        $email = $this->firstNonEmpty(
            $ssoUser['email'] ?? null,
            $userMaster['email'] ?? null
        );

        return array_merge($ssoUser, [
            'user_id' => $this->firstNonEmpty($ssoUser['user_id'] ?? null, $ssoUser['id'] ?? null),
            'username' => $username,
            'full_name' => $fullName,
            'name' => $this->firstNonEmpty($ssoUser['name'] ?? null, $fullName, $username, $email),
            'email' => $email,
            'role_name' => $this->firstNonEmpty($ssoUser['role_name'] ?? null, $userMaster['role_name'] ?? null),
            'avatar' => $this->firstNonEmpty($ssoUser['avatar'] ?? null, $userMaster['avatar'] ?? null),
            'identity_number' => $this->firstNonEmpty($ssoUser['identity_number'] ?? null, $userMaster['nik'] ?? null, $userMaster['identity_number'] ?? null),
            'faculty' => $this->firstNonEmpty($ssoUser['faculty'] ?? null, $userMaster['faculty'] ?? null),
            'study_program' => $this->firstNonEmpty($ssoUser['study_program'] ?? null, $userMaster['study_program'] ?? null, $userMaster['program'] ?? null),
        ]);
    }

    private function firstNonEmpty(...$values): ?string
    {
        foreach ($values as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $trimmed = trim((string) $value);

            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
    }

    private function extractRoleNamesFromSSO(array $ssoUser): array
    {
        return collect([
            $ssoUser['role'] ?? null,
            $ssoUser['role_name'] ?? null,
            data_get($ssoUser, 'access.primary_role'),
            data_get($ssoUser, 'access.roles'),
            $ssoUser['roles'] ?? null,
        ])->filter()
            ->flatMap(function ($candidate) {
                if (is_string($candidate)) {
                    return [$candidate];
                }

                if (! is_array($candidate)) {
                    return [];
                }

                return collect($candidate)->flatMap(function ($item) {
                    if (is_string($item)) {
                        return [$item];
                    }

                    if (! is_array($item)) {
                        return [];
                    }

                    return array_filter([
                        $item['name'] ?? null,
                        $item['code'] ?? null,
                        $item['slug'] ?? null,
                    ]);
                })->all();
            })
            ->map(fn ($role) => strtolower(trim((string) $role)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function extractPermissionNamesFromSSO(array $ssoUser): array
    {
        return collect([
            $ssoUser['permissions'] ?? null,
            data_get($ssoUser, 'access.permissions'),
            collect($ssoUser['roles'] ?? [])->flatMap(function ($role) {
                if (! is_array($role)) {
                    return [];
                }

                return $role['permissions'] ?? [];
            })->all(),
        ])->filter()
            ->flatMap(function ($candidate) {
                if (is_string($candidate)) {
                    return [$candidate];
                }

                if (! is_array($candidate)) {
                    return [];
                }

                return collect($candidate)->flatMap(function ($item) {
                    if (is_string($item)) {
                        return [$item];
                    }

                    if (! is_array($item)) {
                        return [];
                    }

                    return array_filter([
                        $item['name'] ?? null,
                        $item['code'] ?? null,
                        $item['slug'] ?? null,
                    ]);
                })->all();
            })
            ->map(fn ($permission) => strtolower(trim((string) $permission)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
