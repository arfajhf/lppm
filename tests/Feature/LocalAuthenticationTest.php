<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    private function enable(): void
    {
        $this->app->instance('env', 'local');
        config(['local_auth.enabled' => true]);
    }

    private function user(): User
    {
        return User::factory()->create(['email' => 'operator@example.ex', 'role' => 'dosen', 'password' => 'PrivatePassword!2026', 'sso_roles' => [], 'sso_permissions' => []]);
    }

    public function test_local_password_login_preserves_role_and_logout_stays_local(): void
    {
        $this->enable();
        $user = $this->user();
        $this->get(route('login'))->assertOk()->assertSee('Login lokal sementara')->assertSee('Login dengan SSO');
        $this->withSession(['sso_token' => 'old-token'])->post(route('auth.local.login'), ['email' => $user->email, 'password' => 'PrivatePassword!2026', 'role' => 'super_admin'])->assertRedirect(route('dashboard'))->assertSessionHas('authentication_method', 'local')->assertSessionMissing('sso_token');
        $this->assertAuthenticatedAs($user);
        $this->assertSame('dosen', $user->fresh()->role);
        $this->get(route('dashboard'))->assertOk();
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_invalid_credentials_do_not_authenticate_or_flash_password(): void
    {
        $this->enable();
        $user = $this->user();
        $this->post(route('auth.local.login'), ['email' => $user->email, 'password' => 'incorrect-password'])->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_local_auth_is_hidden_and_blocked_in_production_even_when_enabled(): void
    {
        $this->app->instance('env', 'production');
        config(['local_auth.enabled' => true]);
        $this->get(route('login'))->assertOk()->assertDontSee('Login lokal sementara');
        $this->post(route('auth.local.login'), [])->assertNotFound();
        $this->artisan('auth:local-user', ['email' => 'person@example.ex', '--name' => 'Person'])->assertExitCode(1);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_disabled_flag_and_remote_requests_are_blocked(): void
    {
        $this->enable();
        config(['local_auth.enabled' => false]);
        $this->post(route('auth.local.login'), [])->assertNotFound();
        config(['local_auth.enabled' => true]);
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])->post(route('auth.local.login'), [])->assertNotFound();
    }

    public function test_local_sessions_are_revoked_when_flag_is_disabled(): void
    {
        $this->enable();
        $user = $this->user();
        $this->post(route('auth.local.login'), ['email' => $user->email, 'password' => 'PrivatePassword!2026'])->assertRedirect();
        config(['local_auth.enabled' => false]);
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_sso_sessions_keep_the_existing_sso_logout(): void
    {
        $this->enable();
        $this->actingAs($this->user())->post(route('logout'))->assertRedirect(rtrim(config('services.sso.url'), '/').'/logout?redirect='.urlencode(route('login')));
    }

    public function test_local_login_requires_csrf(): void
    {
        $this->enable();
        $this->withMiddleware(VerifyCsrfToken::class);
        $this->post(route('auth.local.login'), ['email' => 'operator@example.ex', 'password' => 'password'])->assertStatus(419);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        $this->enable();
        for ($i = 0; $i < 6; $i++) {
            $this->post(route('auth.local.login'), ['email' => 'absent@example.ex', 'password' => 'incorrect-password'])->assertSessionHasErrors('email');
        }
        $this->post(route('auth.local.login'), ['email' => 'absent@example.ex', 'password' => 'incorrect-password'])->assertStatus(429);
    }

    public function test_command_creates_named_user_and_refuses_to_overwrite_existing_account(): void
    {
        $this->enable();
        $this->artisan('auth:local-user', ['email' => 'person@example.ex', '--name' => 'Person', '--role' => 'dosen', '--faculty' => 'Teknik', '--program' => 'Informatika'])
            ->expectsQuestion('Password akun (minimal 12 karakter)', 'PrivatePassword!2026')->expectsQuestion('Ulangi password', 'PrivatePassword!2026')->assertExitCode(0);
        $this->assertDatabaseHas('users', ['email' => 'person@example.ex', 'name' => 'Person', 'role' => 'dosen', 'faculty' => 'Teknik', 'study_program' => 'Informatika']);
        $this->artisan('auth:local-user', ['email' => 'person@example.ex', '--name' => 'Changed', '--role' => 'super_admin'])->assertExitCode(1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['email' => 'person@example.ex', 'role' => 'dosen']);
    }
}
