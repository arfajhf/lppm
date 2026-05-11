@extends('layouts.app')

@section('title', 'Dokumen | UMB LPPM')
@section('eyebrow', 'Dokumen')
@section('page_title', 'Dokumen Proposal')

@section('content')
    <section class="surface-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">{{ $module['eyebrow'] }}</p>
                <h2>{{ $module['title'] }}</h2>
            </div>
        </div>
        <p class="surface-copy">{{ $module['description'] }}</p>
        <div class="module-focus-list">
            @foreach (($module['focus'] ?? []) as $focus)
                <div class="module-focus-item">{{ $focus }}</div>
            @endforeach
        </div>
        <div class="status-chips">
            @forelse ($documentTypeSummary as $documentType => $total)
                <span>{{ ucfirst($documentType ?: 'lainnya') }}: {{ $total }}</span>
            @empty
            @endforelse
        </div>
        <div class="table-stack">
            @forelse ($documents as $document)
                <div class="table-row">
                    <div>
                        <strong>{{ $document->file_name }}</strong>
                        <span>{{ $document->proposal?->title }} &middot; {{ ucfirst($document->document_type) }}</span>
                    </div>
                    <div class="table-row__meta">
                        <span class="badge">{{ $document->proposal?->status ? ucfirst($document->proposal->status) : 'Tanpa status' }}</span>
                        <small>{{ $document->uploader?->display_full_name ?? $document->uploader?->name ?? '-' }}</small>
                    </div>
                </div>
            @empty
                <p class="empty-copy">{{ $module['empty'] }}</p>
            @endforelse
        </div>
    </section>

    <div class="pagination-wrap">
        {{ $documents->links() }}
    </div>
@endsection
