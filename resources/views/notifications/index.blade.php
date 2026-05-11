@extends('layouts.app')

@section('title', 'Notifikasi | UMB LPPM')
@section('eyebrow', 'Notifikasi')
@section('page_title', 'Notifikasi LPPM')

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
            @forelse ($channelSummary as $channel => $total)
                <span>{{ strtoupper($channel ?: 'system') }}: {{ $total }}</span>
            @empty
            @endforelse
        </div>
        <div class="review-stack">
            @forelse ($notifications as $notification)
                <article class="review-card">
                    <strong>{{ $notification->user?->display_full_name ?? $notification->user?->name ?? 'Sistem' }}</strong>
                    <span>{{ strtoupper($notification->channel) }} &middot; {{ $notification->is_read ? 'Sudah dibaca' : 'Belum dibaca' }}</span>
                    <p>{{ $notification->message }}</p>
                </article>
            @empty
                <p class="empty-copy">{{ $module['empty'] }}</p>
            @endforelse
        </div>
    </section>

    <div class="pagination-wrap">
        {{ $notifications->links() }}
    </div>
@endsection
