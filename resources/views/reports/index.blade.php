@extends('layouts.app')

@section('title', 'Laporan | UMB LPPM')
@section('eyebrow', 'Laporan')
@section('page_title', 'Monitoring Laporan Progres')

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
            <span>0-25%: {{ $progressBuckets['0-25'] }}</span>
            <span>26-75%: {{ $progressBuckets['26-75'] }}</span>
            <span>76-100%: {{ $progressBuckets['76-100'] }}</span>
        </div>
        <div class="table-stack">
            @forelse ($reports as $report)
                <div class="table-row">
                    <div>
                        <strong>{{ $report->proposal?->title }}</strong>
                        <span>{{ $report->description ?: 'Belum ada deskripsi progres.' }}</span>
                    </div>
                    <div class="table-row__meta">
                        <span class="badge">{{ $report->progress }}%</span>
                        <small>{{ optional($report->submitted_at)->format('d M Y H:i') ?: '-' }}</small>
                    </div>
                </div>
            @empty
                <p class="empty-copy">{{ $module['empty'] }}</p>
            @endforelse
        </div>
    </section>

    <div class="pagination-wrap">
        {{ $reports->links() }}
    </div>
@endsection
