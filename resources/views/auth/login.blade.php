@extends('layouts.auth')

@section('title', 'Login | UMB LPPM')
@section('auth_title', 'Masuk ke ruang kerja LPPM yang modern, ringkas, dan siap menampung banyak proposal tanpa drama folder warisan.')

@section('content')
    <div class="form-card">
        <div class="form-card__header">
            <p class="eyebrow">Login</p>
            <h2>Akses akun Anda</h2>
            <p>Layout login ini mengikuti semangat UMB Space: fokus, visual kuat, dan langsung mengantar pengguna ke jalur SSO resmi.</p>
        </div>

        @if (session('error') || request()->filled('error'))
            <div class="alert alert--danger">{{ session('error') ?: request()->query('error') }}</div>
        @endif

        @if (session('status'))
            <div class="alert alert--success">{{ session('status') }}</div>
        @endif

        @if (session('success') || request()->filled('success'))
            <div class="alert alert--success">{{ session('success') ?: request()->query('success') }}</div>
        @endif

        @if(\App\Support\LocalAuthentication::available(request()))
            <form method="POST" action="{{ route('auth.local.login') }}" class="form-stack">
                @csrf
                <h3>Login lokal sementara</h3>
                <p>Gunakan akun pribadi yang dibuat untuk pemeriksaan aplikasi lokal.</p>
                @if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
                <label class="field">Email<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required maxlength="255"></label>
                <label class="field">Password<input type="password" name="password" autocomplete="current-password" required></label>
                <button class="btn btn--primary btn--block">Masuk dengan akun lokal</button>
            </form>
        @endif

        <a href="{{ route('sso.redirect') }}" class="btn btn--primary btn--block">Login dengan SSO</a>

        <div class="auth-links">
            <a href="{{ $registerUrl }}" class="text-link">Daftar</a>
            <a href="{{ $forgotPasswordUrl }}" class="text-link">Lupa password</a>
        </div>

        <div class="helper-card">
            <strong>Catatan auth</strong>
            <p>Akun dikelola melalui UMB SSO. Pendaftaran baru dan perubahan password mengikuti alur pusat yang sama dengan UMB Space.</p>
        </div>
    </div>
@endsection
