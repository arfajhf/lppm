<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

class LppmAccess
{
    public static function roleAliases(): array
    {
        return [
            'admin' => 'super_admin',
            'superadmin' => 'super_admin',
            'admin_lppm' => 'lppm_admin',
            'yayasan' => 'rektorat',
        ];
    }

    public static function expandRoleNames(array $roleNames): array
    {
        $aliases = self::roleAliases();

        return collect($roleNames)
            ->map(fn ($role) => strtolower(trim((string) $role)))
            ->filter()
            ->flatMap(function (string $role) use ($aliases) {
                $expanded = [$role];

                if (isset($aliases[$role])) {
                    $expanded[] = $aliases[$role];
                }

                return $expanded;
            })
            ->unique()
            ->values()
            ->all();
    }

    public static function requiredPermissions(): array
    {
        return config('lppm_access.required_permissions', []);
    }

    public static function requiredRoles(): array
    {
        return config('lppm_access.required_roles', []);
    }

    public static function fallbackPermissionsForRoles(array $roleNames): array
    {
        $fallbacks = config('lppm_access.role_permission_fallbacks', []);

        return collect(self::expandRoleNames($roleNames))
            ->flatMap(fn ($role) => $fallbacks[$role] ?? [])
            ->unique()
            ->values()
            ->all();
    }

    public static function navigationFor(User $user): Collection
    {
        $workspace = LppmWorkspace::forUser($user);
        $labelMap = $workspace['navigation_labels'] ?? [];

        return collect(config('lppm_access.navigation', []))
            ->filter(fn (array $item) => $user->hasLppmPermission($item['permission'] ?? ''))
            ->map(function (array $item) use ($labelMap) {
                $routeName = $item['route'] ?? '';

                if (isset($labelMap[$routeName])) {
                    $item['label'] = $labelMap[$routeName];
                }

                return $item;
            })
            ->values();
    }

    public static function currentSsoAccessSnapshot(): array
    {
        return [
            'roles' => [
                [
                    'name' => 'super_admin',
                    'permissions' => [
                        'api.view',
                        'employee.view',
                        'lecturer.view',
                        'lppm.access',
                        'lppm.access.manage',
                        'lppm.dashboard.view',
                        'lppm.document.upload',
                        'lppm.document.view',
                        'lppm.notification.view',
                        'lppm.proposal.create',
                        'lppm.proposal.submit',
                        'lppm.proposal.update',
                        'lppm.proposal.view',
                        'lppm.proposal.view_all',
                        'lppm.report.create',
                        'lppm.report.view',
                        'lppm.review.approve',
                        'lppm.review.assign',
                        'lppm.review.score',
                        'lppm.review.view',
                        'permission.view',
                        'role.create',
                        'role.view',
                        'settings.view',
                        'student.view',
                        'user.create',
                        'user.delete',
                        'user.edit',
                        'user.view',
                        'yayasan.view',
                    ],
                ],
                [
                    'name' => 'lppm_admin',
                    'permissions' => [
                        'lppm.access',
                        'lppm.access.manage',
                        'lppm.dashboard.view',
                        'lppm.document.upload',
                        'lppm.document.view',
                        'lppm.notification.view',
                        'lppm.proposal.create',
                        'lppm.proposal.submit',
                        'lppm.proposal.update',
                        'lppm.proposal.view',
                        'lppm.proposal.view_all',
                        'lppm.report.create',
                        'lppm.report.view',
                        'lppm.review.approve',
                        'lppm.review.assign',
                        'lppm.review.score',
                        'lppm.review.view',
                    ],
                ],
                [
                    'name' => 'dosen',
                    'permissions' => [
                        'lecturer.view',
                        'lppm.access',
                        'lppm.dashboard.view',
                        'lppm.document.upload',
                        'lppm.document.view',
                        'lppm.notification.view',
                        'lppm.proposal.create',
                        'lppm.proposal.submit',
                        'lppm.proposal.update',
                        'lppm.proposal.view',
                        'lppm.report.create',
                        'lppm.report.view',
                        'lppm.review.view',
                        'student.view',
                    ],
                ],
                [
                    'name' => 'mahasiswa',
                    'permissions' => [
                        'lppm.access',
                        'lppm.dashboard.view',
                        'lppm.document.upload',
                        'lppm.document.view',
                        'lppm.notification.view',
                        'lppm.proposal.view',
                        'lppm.report.view',
                        'student.view',
                    ],
                ],
                [
                    'name' => 'kaprodi',
                    'permissions' => [
                        'lecturer.view',
                        'lppm.access',
                        'lppm.dashboard.view',
                        'lppm.document.upload',
                        'lppm.document.view',
                        'lppm.notification.view',
                        'lppm.proposal.view',
                        'lppm.proposal.create',
                        'lppm.proposal.submit',
                        'lppm.proposal.update',
                        'lppm.report.create',
                        'lppm.report.view',
                        'lppm.review.view',
                    ],
                ],
                [
                    'name' => 'dekan',
                    'permissions' => [
                        'lecturer.view',
                        'lppm.access',
                        'lppm.dashboard.view',
                        'lppm.document.upload',
                        'lppm.document.view',
                        'lppm.notification.view',
                        'lppm.proposal.view',
                        'lppm.proposal.create',
                        'lppm.proposal.submit',
                        'lppm.proposal.update',
                        'lppm.report.create',
                        'lppm.report.view',
                        'lppm.review.view',
                    ],
                ],
                [
                    'name' => 'rektorat',
                    'permissions' => [
                        'lppm.access',
                        'lppm.dashboard.view',
                        'lppm.notification.view',
                        'lppm.proposal.view',
                        'lppm.report.view',
                        'yayasan.view',
                    ],
                ],
            ],
            'permissions' => [
                'api.view',
                'employee.view',
                'lecturer.view',
                'lppm.access',
                'lppm.access.manage',
                'lppm.dashboard.view',
                'lppm.document.upload',
                'lppm.document.view',
                'lppm.notification.view',
                'lppm.proposal.create',
                'lppm.proposal.submit',
                'lppm.proposal.update',
                'lppm.proposal.view',
                'lppm.proposal.view_all',
                'lppm.report.create',
                'lppm.report.view',
                'lppm.review.approve',
                'lppm.review.assign',
                'lppm.review.score',
                'lppm.review.view',
                'permission.view',
                'role.create',
                'role.view',
                'settings.view',
                'student.view',
                'user.create',
                'user.delete',
                'user.edit',
                'user.view',
                'yayasan.view',
            ],
        ];
    }

    public static function missingSsoRoles(): array
    {
        $existing = collect(self::currentSsoAccessSnapshot()['roles'])->pluck('name')->map(fn ($role) => strtolower((string) $role));

        return collect(array_keys(self::requiredRoles()))
            ->reject(fn ($role) => $existing->contains(strtolower($role)))
            ->values()
            ->all();
    }

    public static function missingSsoPermissions(): array
    {
        $existing = collect(self::currentSsoAccessSnapshot()['permissions'])->map(fn ($permission) => strtolower((string) $permission));

        return collect(array_keys(self::requiredPermissions()))
            ->reject(fn ($permission) => $existing->contains(strtolower($permission)))
            ->values()
            ->all();
    }
}
