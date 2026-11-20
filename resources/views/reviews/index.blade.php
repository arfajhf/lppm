@extends('layouts.app')

@section('title', 'Review | UMB LPPM')
@section('eyebrow', 'Review')
@section('page_title', 'Daftar Review Proposal')

@section('content')
    <section class="surface-card"><h2>Tugas penilaian hibah</h2>
        @forelse($grantTasks as $task)
            @php $assessment=$task->proposal->grantRecords()->where('kind','assessment')->where('data->assignment_id',$task->id)->latest('id')->first(); @endphp
            <article class="review-card"><a href="{{ route('grants.show',$task->proposal) }}">{{ $task->proposal->title }}</a>
            <p>{{ ['proposal'=>'Review proposal','monev'=>'Monev','seminar'=>'Seminar hasil'][$task->data['review_kind']] ?? '' }} · {{ $assessment ? 'Penilaian tersimpan' : 'Menunggu penilaian' }} · Jadwal {{ $task->data['event_date'] ?? '-' }}</p></article>
        @empty<p>Belum ada tugas hibah aktif dalam lingkup Anda.</p>@endforelse
        {{ $grantTasks->links() }}
    </section>
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
            @forelse ($statusSummary as $status => $total)
                <span>{{ ucfirst($status ?: 'baru') }}: {{ $total }}</span>
            @empty
            @endforelse
        </div>
        <div class="review-stack">
            @forelse ($reviews as $review)
                <article class="review-card">
                    <strong>{{ $review->proposal?->title }}</strong>
                    <span>{{ $review->reviewer?->name }} &middot; Skor {{ $review->score ?? '-' }} &middot; {{ ucfirst($review->status) }}</span>
                    <p>{{ $review->comment ?: 'Belum ada catatan detail.' }}</p>
                </article>
            @empty
                <p class="empty-copy">{{ $module['empty'] }}</p>
            @endforelse
        </div>
    </section>

    <div class="pagination-wrap">
        {{ $reviews->links() }}
    </div>
@endsection
