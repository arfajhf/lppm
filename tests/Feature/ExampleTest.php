<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('UMB LPPM');
    }

    public function test_login_screen_is_available(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Akses akun Anda');
        $response->assertSee('Login dengan SSO');
        $response->assertDontSee('Role demo');
        $response->assertDontSee('Mode latihan lokal');
        $this->post('/local-preview/login', ['role' => 'lppm_admin'])->assertNotFound();
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Ringkasan Operasional LPPM');
    }

    public function test_sso_callback_logs_user_into_the_app(): void
    {
        config(['services.sso.url' => 'http://localhost/umb-sso/public']);
        Http::fake([
            'http://localhost/umb-sso/public/api/sso/token' => Http::response([
                'access_token' => 'token-test',
                'user' => [
                    'user_id' => 'sso-123',
                    'username' => 'dosen1',
                    'full_name' => 'Dosen SSO',
                    'email' => 'dosen.sso@umb.ac.id',
                    'role' => 'dosen',
                ],
            ], 200),
            'http://localhost/umb-sso/public/api/*' => Http::response([
                'user' => [
                    'user_id' => 'sso-123',
                    'username' => 'dosen1',
                    'full_name' => 'Dosen SSO',
                    'email' => 'dosen.sso@umb.ac.id',
                    'role' => 'dosen',
                ],
            ], 200),
        ]);

        $response = $this->withSession(['sso_state' => 'test-state'])->get(route('sso.callback', ['code' => 'test-code', 'state' => 'test-state']));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'sso_user_id' => 'sso-123',
            'email' => 'dosen.sso@umb.ac.id',
        ]);
    }
}
