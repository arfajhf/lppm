@extends('layouts.base')
@section('title','Pengumuman | UMB LPPM')
@section('body')
<main class="landing-shell">
    <section class="surface-card">
        <p class="eyebrow">Universitas Mayasari Bakti</p><h1>Pengumuman LPPM</h1>
        <a class="btn btn--primary" href="{{ route('login') }}">Masuk untuk mengajukan</a>
        @forelse($calls as $call)
            <article class="helper-card"><h2>{{ $call->title }}</h2><p>{{ $call->guidelines }}</p>
                <p>Penerimaan {{ $call->opens_at->format('d M Y') }} – {{ $call->closes_at->format('d M Y') }} · {{ $call->isOpen() ? 'Dibuka' : 'Ditutup / belum dibuka' }}</p>
                @if($call->announcement_path)<a href="{{ route('grants.public-announcement',$call) }}">Unduh surat pengumuman dan panduan</a>@endif
            </article>
        @empty<p>Belum ada pengumuman penerimaan.</p>@endforelse
    </section>
    <section class="surface-card"><h2>Hasil penetapan reviewer</h2>
        @forelse($reviewers as $reviewer)<article class="helper-card"><strong>{{ $reviewer->user->display_full_name }}</strong><p>{{ $reviewer->expertise }} · {{ $reviewer->institution }} · {{ $reviewer->origin }}</p><p>SK {{ $reviewer->decree_number }} · Berlaku sampai {{ $reviewer->valid_until->format('d M Y') }}</p><a href="{{ route('grants.reviewer-announcement',[$reviewer,'decree']) }}">SK reviewer</a>
@if($reviewer->origin==='internal')<a href="{{ route('grants.reviewer-announcement',[$reviewer,'result_announcement']) }}">Surat pengumuman kelulusan</a>@endif
</article>
        @empty<p>Belum ada hasil penetapan yang diumumkan.</p>@endforelse
    </section>
</main>
@endsection
