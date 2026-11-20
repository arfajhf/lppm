<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateLocalUser extends Command
{
    protected $signature = 'auth:local-user {email} {--name=} {--role=dosen} {--faculty=} {--program=} {--generate-password}';

    protected $description = 'Buat akun lokal bernama dengan password pribadi, hanya untuk lingkungan lokal.';

    public function handle(): int
    {
        if (! app()->environment('local') || ! config('local_auth.enabled')) {
            $this->error('Pembuatan akun lokal tidak aktif pada lingkungan ini.');

            return self::FAILURE;
        }
        $email = strtolower(trim($this->argument('email')));
        if (User::where('email', $email)->exists()) {
            $this->error('Akun sudah ada. Akun dan role yang ada tidak diubah.');

            return self::FAILURE;
        }
        $data = ['email' => $email, 'name' => $this->option('name') ?: $this->ask('Nama lengkap'), 'role' => $this->option('role'), 'faculty' => $this->option('faculty'), 'study_program' => $this->option('program')];
        $password = $this->option('generate-password') ? bin2hex(random_bytes(18)) : $this->secret('Password akun (minimal 12 karakter)');
        if (! $this->option('generate-password') && $password !== $this->secret('Ulangi password')) {
            $this->error('Konfirmasi password tidak sama.');

            return self::FAILURE;
        }
        $data = Validator::make($data + ['password' => $password], ['email' => 'required|email|max:255', 'name' => 'required|string|max:255', 'role' => ['required', Rule::in(array_keys(config('lppm_access.role_permission_fallbacks')))], 'faculty' => 'nullable|string|max:255', 'study_program' => 'nullable|string|max:255', 'password' => 'required|string|min:12|max:1024'])->validate();
        $path = null;
        try {
            DB::transaction(function () use ($data, $password, &$path) {
                $user = User::create(array_merge($data, ['full_name' => $data['name'], 'password' => Hash::make($password), 'is_admin' => false, 'sso_roles' => [], 'sso_permissions' => []]));
                if ($this->option('generate-password')) {
                    $directory = storage_path('app/local-auth');
                    if (! is_dir($directory) && ! mkdir($directory, 0700, true)) {
                        throw new \RuntimeException('Direktori kredensial tidak dapat dibuat.');
                    }
                    chmod($directory, 0700);
                    $path = $directory.'/'.$user->user_id.'.txt';
                    $handle = fopen($path, 'x');
                    if (! $handle) {
                        throw new \RuntimeException('Berkas kredensial tidak dapat dibuat.');
                    }
                    chmod($path, 0600);
                    fwrite($handle, "Login lokal LPPM\nURL: ".url('/login')."\nNama: {$user->name}\nEmail: {$user->email}\nRole: {$user->role}\nPassword: {$password}\n");
                    fclose($handle);
                }
            });
        } catch (\Throwable $exception) {
            if ($path && is_file($path)) {
                unlink($path);
            }
            throw $exception;
        }
        $this->info('Akun lokal dibuat. Tidak ada data hibah yang ditambahkan.');
        if ($path) {
            $this->line('Kredensial privat: '.$path);
        }

        return self::SUCCESS;
    }
}
