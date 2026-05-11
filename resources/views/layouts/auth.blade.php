@extends('layouts.base')

@section('body_class', 'lppm-auth-page')

@section('body')
    <main class="auth-shell">
        <div class="auth-shell__glow auth-shell__glow--left"></div>
        <div class="auth-shell__glow auth-shell__glow--right"></div>
        <div class="auth-shell__grid">
            <section class="auth-panel auth-panel--showcase">
                <div class="brand-pill">
                    <img src="{{ url('images/logos/logo_circle_128.png') }}" alt="Logo Universitas Mayasari Bakti">
                    <span>Universitas Mayasari Bakti</span>
                </div>

                <div class="auth-hero">
                    <img src="{{ url('images/logos/logo_lppm.jpg') }}" alt="Logo UMB LPPM" class="auth-hero__logo">
                    <p class="eyebrow">Sistem Terintegrasi LPPM</p>
                    <h1>@yield('auth_title', 'Kelola penelitian dan pengabdian dalam satu ruang kerja yang rapi.')</h1>
                    <p class="auth-copy">
                        Platform ini dirancang untuk admin, reviewer, dosen, mahasiswa, dan mitra agar proposal, dokumen, review, hingga laporan berjalan dalam satu ritme yang tertib.
                    </p>
                </div>

                <div class="auth-metrics">
                    <article class="metric-card">
                        <strong>01</strong>
                        <span>Proposal penelitian dan pengabdian terkonsolidasi.</span>
                    </article>
                    <article class="metric-card">
                        <strong>02</strong>
                        <span>Alur review dan revisi terasa lebih jelas, tidak lagi hidup liar di folder acak.</span>
                    </article>
                    <article class="metric-card">
                        <strong>03</strong>
                        <span>Siap dihubungkan ke SSO, API, dan dashboard monitoring berikutnya.</span>
                    </article>
                </div>
            </section>

            <section class="auth-panel auth-panel--form">
                @yield('content')
            </section>
        </div>
    </main>
@endsection
