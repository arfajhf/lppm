@extends('layouts.app')

@section('title', 'Dashboard | UMB LPPM')
@section('eyebrow', 'Dashboard')
@section('page_title', 'Ringkasan Operasional LPPM')

@section('content')
    <section class="surface-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">{{ $primaryRoleLabel }}</p>
                <h2>{{ $dashboardTitle }}</h2>
            </div>
        </div>
        <p class="surface-copy">{{ $dashboardDescription }}</p>
        <div class="proposal-grid">
            @foreach ($focusCards as $card)
                <article class="proposal-card surface-card">
                    <h3>{{ $card['title'] }}</h3>
                    <p>{{ $card['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="stats-grid">
        <article class="surface-card stat-card">
            <span>Total Proposal</span>
            <strong>{{ $stats['total'] }}</strong>
            <p>Jumlah proposal yang memang relevan dengan ruang kerja Anda.</p>
        </article>
        <article class="surface-card stat-card">
            <span>Menunggu Proses</span>
            <strong>{{ $stats['submitted'] }}</strong>
            <p>Proposal yang sedang menunggu persetujuan kaprodi, dekan, atau penerimaan LPPM.</p>
        </article>
        <article class="surface-card stat-card">
            <span>Dalam Review</span>
            <strong>{{ $stats['in_review'] }}</strong>
            <p>Proposal aktif yang sedang dievaluasi atau butuh perhatian lanjutan.</p>
        </article>
        <article class="surface-card stat-card">
            <span>Selesai</span>
            <strong>{{ $stats['completed'] }}</strong>
            <p>Kegiatan yang sudah menuntaskan siklusnya dan siap dijadikan pelajaran baik.</p>
        </article>
    </section>

    <section class="content-grid">
        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Peran Aktif</p>
                    <h2>Aksi prioritas untuk {{ $user->display_full_name }}</h2>
                </div>
            </div>
            <div class="list-stack">
                @foreach ($roleActions as $action)
                    <div class="list-item">
                        <span class="list-item__dot"></span>
                        <p>{{ $action }}</p>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Distribusi Status</p>
                    <h2>Komposisi proposal saat ini</h2>
                </div>
            </div>
            <div class="status-chips">
                @forelse ($statusSummary as $status => $total)
                    <span>{{ \App\Models\Proposal::labelForStatus($status) }}: {{ $total }}</span>
                @empty
                    <p class="empty-copy">Belum ada data status. Tenang, ini bukan error eksistensial.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="content-grid">
        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Proposal Terbaru</p>
                    <h2>{{ $proposalHeading }}</h2>
                </div>
                @if (auth()->user()->hasLppmPermission('lppm.proposal.create'))
                    <a href="{{ route('proposals.create') }}" class="btn btn--secondary">Buat Proposal</a>
                @endif
            </div>
            <div class="table-stack">
                @forelse ($latestProposals as $proposal)
                    <a href="{{ route('proposals.show', $proposal) }}" class="table-row">
                        <div>
                            <strong>{{ $proposal->title }}</strong>
                            <span>{{ $proposal->type }} &middot; {{ $proposal->scheme }}</span>
                        </div>
                        <div class="table-row__meta">
                            <span class="badge">{{ \App\Models\Proposal::labelForStatus($proposal->status) }}</span>
                            <small>{{ $proposal->user?->name }}</small>
                        </div>
                    </a>
                @empty
                    <p class="empty-copy">Belum ada proposal yang tercatat.</p>
                @endforelse
            </div>
        </article>

        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Review Terbaru</p>
                    <h2>{{ $reviewHeading }}</h2>
                </div>
            </div>
            <div class="review-stack">
                @forelse ($latestReviews as $review)
                    <article class="review-card">
                        <strong>{{ $review->proposal?->title }}</strong>
                        <span>{{ $review->reviewer?->name }} &middot; {{ ucfirst($review->status) }}</span>
                        <p>{{ $review->comment ?: 'Belum ada catatan detail.' }}</p>
                    </article>
                @empty
                    <p class="empty-copy">Belum ada review terbaru.</p>
                @endforelse
            </div>
        </article>
    </section>
@endsection
