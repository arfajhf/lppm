<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrantRoleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_navigation_pages_render_for_each_authorized_role(): void
    {
        $this->withoutVite();
        foreach (['lppm_admin', 'dosen', 'kaprodi', 'dekan', 'reviewer', 'rektor', 'wakil_rektor', 'keuangan', 'kepala_lppm', 'sekretaris_lppm', 'mahasiswa'] as $role) {
            $user = User::factory()->create(['role' => $role, 'faculty' => 'Teknik', 'study_program' => 'Informatika', 'sso_roles' => [], 'sso_permissions' => []]);
            foreach (config('lppm_access.navigation') as $nav) {
                if ($user->hasLppmPermission($nav['permission'])) {
                    $this->actingAs($user)->get(route($nav['route']))->assertOk();
                }
            }
            $this->actingAs($user)->get(route('reviewer-appointments.index'))->assertOk();
        }
    }
}
