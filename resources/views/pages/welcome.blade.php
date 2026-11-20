@extends('layouts.base')

@section('title', 'UMB LPPM')

@section('body')
    <main class="landing-shell">
        <section class="hero">
            <div class="hero__content">
                <div class="brand-pill">
                    <img src="{{ url('images/logos/logo_circle_128.png') }}" alt="Logo Universitas">
                    <span>Universitas Mayasari Bakti</span>
                </div>
                <p class="eyebrow">Blueprint-driven platform</p>
                <h1><strong><span style="color:green">UMB</span><span style="color: red">:</span>LPPM untuk penelitian dan pengabdian yang lebih terstruktur.</h1>
                <p class="hero__copy">
                    Sistem ini dibangun berdasarkan blueprint LPPM untuk memusatkan proposal, dokumen, review, laporan, dan notifikasi dalam satu pengalaman kerja yang modern dan mudah diperluas.
                </p>
                <div class="hero__actions">
                    <a href="{{ route('grants.announcements') }}" class="btn btn--secondary">Pengumuman hibah dan reviewer</a>
                    <a href="{{ route('login') }}" class="btn btn--primary">Masuk ke Sistem</a>
                </div>
            </div>

            <div class="hero__visual surface-card">
                <div class="hero__visual-head">
                    
                    <div>
                        <strong><span style="color:green">UMB</span><span style="color: red">:</span>LPPM Workspace</strong>
                        <span>Proposal, review, reporting</span>
                    </div>
                </div>
                <div class="workflow-pillars">
                    @foreach ($workflow as $step)
                        <span>{{ \App\Models\Proposal::labelForStatus($step) }}</span>
                    @endforeach
                </div>
                <div class="feature-stack">
                    @foreach ($featureGroups as $feature)
                        <article class="feature-card">
                            <h3>{{ $feature['title'] }}</h3>
                            <p>{{ $feature['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </main>
@endsection
