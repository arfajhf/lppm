<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\UsesUuid;
use App\Support\LppmAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use UsesUuid;

    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'sso_user_id',
        'username',
        'full_name',
        'name',
        'email',
        'role',
        'identity_number',
        'faculty',
        'study_program',
        'avatar',
        'is_admin',
        'sso_roles',
        'sso_permissions',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'sso_roles' => 'array',
        'sso_permissions' => 'array',
    ];

    protected $appends = [
        'display_username',
        'display_full_name',
    ];

    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'user_id', 'user_id');
    }

    public function proposalMemberships()
    {
        return $this->hasMany(ProposalMember::class, 'user_id', 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id', 'user_id');
    }

    public function activityNotifications()
    {
        return $this->hasMany(ActivityNotification::class, 'user_id', 'user_id');
    }

    public function getDisplayUsernameAttribute(): string
    {
        return trim((string) ($this->username ?: $this->name ?: $this->email ?: $this->sso_user_id ?: $this->getAuthIdentifier()));
    }

    public function getDisplayFullNameAttribute(): string
    {
        return trim((string) ($this->full_name ?: $this->name ?: $this->email ?: $this->display_username));
    }

    public function hasAdminAccess(): bool
    {
        return $this->is_admin || $this->hasAnyLppmRole(['super_admin', 'admin', 'superadmin', 'admin_lppm', 'lppm_admin']);
    }

    public function getSsoRoleNames(): array
    {
        return collect($this->sso_roles ?? [])
            ->map(fn ($role) => strtolower(trim((string) $role)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function getEffectiveLppmRoles(): array
    {
        return LppmAccess::expandRoleNames(
            array_unique(array_merge($this->getSsoRoleNames(), [strtolower((string) $this->role)]))
        );
    }

    public function getSsoPermissionNames(): array
    {
        return collect($this->sso_permissions ?? [])
            ->map(fn ($permission) => strtolower(trim((string) $permission)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function getEffectiveLppmPermissions(): array
    {
        $permissions = collect($this->getSsoPermissionNames())
            ->filter(fn ($permission) => str_starts_with($permission, 'lppm.'));

        if ($permissions->isNotEmpty()) {
            return $permissions->values()->all();
        }

        return LppmAccess::fallbackPermissionsForRoles($this->getEffectiveLppmRoles());
    }

    public function hasLppmPermission(string $permission): bool
    {
        if ($permission === '') {
            return false;
        }

        return in_array(strtolower($permission), $this->getEffectiveLppmPermissions(), true);
    }

    public function hasAnyLppmPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasLppmPermission((string) $permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyLppmRole(array $roles): bool
    {
        $effectiveRoles = $this->getEffectiveLppmRoles();

        foreach ($roles as $role) {
            if (in_array(strtolower((string) $role), $effectiveRoles, true)) {
                return true;
            }
        }

        return false;
    }
}
