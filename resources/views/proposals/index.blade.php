@extends('layouts.app')

@section('title', 'Proposal | UMB LPPM')
@section('eyebrow', 'Proposal')
@section('page_title', 'Manajemen Proposal')

@section('content')
    <div class="section-heading">
        <div>
            <p class="eyebrow">{{ $module['eyebrow'] }}</p>
            <h2>{{ $module['title'] }}</h2>
        </div>
        @if (auth()->user()->hasLppmPermission('lppm.proposal.create'))
            <a href="{{ route('proposals.create') }}" class="btn btn--primary">{{ $module['cta_label'] ?? 'Tambah Proposal' }}</a>
        @endif
    </div>

    <section class="content-grid">
        <article class="surface-card">
            <p class="surface-copy">{{ $module['description'] }}</p>
            <div class="module-focus-list">
                @foreach (($module['focus'] ?? []) as $focus)
                    <div class="module-focus-item">{{ $focus }}</div>
                @endforeach
            </div>
        </article>

        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Distribusi Status</p>
                    <h2>Posisi proposal di workflow</h2>
                </div>
            </div>
            <div class="status-chips">
                @forelse ($statusSummary as $status => $total)
                    <span>{{ \App\Models\Proposal::labelForStatus($status) }}: {{ $total }}</span>
                @empty
                    <span>Belum ada data status proposal.</span>
                @endforelse
            </div>
        </article>
    </section>

    <section class="proposal-grid">
        @forelse ($proposals as $proposal)
            <article class="surface-card proposal-card">
                <div class="proposal-card__head">
                    <span class="badge">{{ \App\Models\Proposal::labelForStatus($proposal->status) }}</span>
                    <small>{{ $proposal->year }}</small>
                </div>
                <h3>{{ $proposal->title }}</h3>
                <p>{{ $proposal->summary ?: 'Ringkasan proposal belum ditambahkan.' }}</p>
                <div class="meta-pills">
                    <span>{{ $proposal->type }}</span>
                    <span>{{ $proposal->scheme }}</span>
                    <span>Review: {{ $proposal->reviews->count() }}</span>
                </div>
                <a href="{{ route('proposals.show', $proposal) }}" class="text-link">Lihat detail</a>
            </article>
        @empty
            <div class="surface-card empty-state">
                <h3>Belum ada proposal</h3>
                <p>{{ $module['empty'] }}</p>
            </div>
        @endforelse
    </section>

    <div class="pagination-wrap">
        {{ $proposals->links() }}
    </div>
@endsection
