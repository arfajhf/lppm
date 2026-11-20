@extends('layouts.app')

@section('title', $proposal->title . ' | UMB LPPM')
@section('eyebrow', 'Detail Proposal')
@section('page_title', $proposal->title)

@section('content')
    @if(!$proposal->grant_stage && in_array($proposal->type,['Penelitian Hibah','Pengabdian Hibah']) && ($proposal->user_id===auth()->id() || auth()->user()->hasLppmPermission('lppm.grant.manage')))
    <section class="surface-card"><h2>Masukkan proposal lama ke alur SOP hibah</h2><p>Proposal akan dimulai kembali dari draft. Berkas dan riwayat lama tetap tersimpan.</p>
    <form method="POST" action="{{ route('grants.enroll',$proposal) }}" class="form-stack">@csrf
    <label class="field">Periode hibah<select name="grant_call_id" required>@foreach(\App\Models\GrantCall::where('purpose','grant')->where('published',true)->get()->filter(fn($c)=>$c->isOpen()) as $call)<option value="{{ $call->id }}">{{ $call->title }}</option>@endforeach</select></label><button class="btn btn--primary">Mulai alur SOP hibah</button></form></section>
    @endif
    <section class="content-grid">
        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Informasi Utama</p>
                    <h2>{{ $proposal->type }}</h2>
                </div>
                <span class="badge">{{ \App\Models\Proposal::labelForStatus($proposal->status) }}</span>
            </div>
            <div class="detail-grid">
                <div><strong>Skema</strong><span>{{ $proposal->scheme }}</span></div>
                <div><strong>Tahun</strong><span>{{ $proposal->year }}</span></div>
                <div><strong>Pengusul</strong><span>{{ $proposal->user?->display_full_name ?? $proposal->user?->name }}</span></div>
                <div><strong>Ringkasan</strong><span>{{ $proposal->summary ?: 'Belum ada ringkasan.' }}</span></div>
            </div>
        </article>

        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">{{ $module['eyebrow'] }}</p>
                    <h2>Fokus tindak lanjut pada fase ini</h2>
                </div>
            </div>
            <p class="surface-copy">{{ $module['description'] }}</p>
            <div class="list-stack">
                @forelse ($guidance as $item)
                    <div class="list-item">
                        <span class="list-item__dot"></span>
                        <p>{{ $item }}</p>
                    </div>
                @empty
                    <p class="empty-copy">Belum ada panduan khusus untuk fase ini. Status proposal mungkin sedang tenang, atau kita yang harus lebih jeli.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="content-grid">
        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Approval</p>
                    <h2>Jejak persetujuan proposal</h2>
                </div>
            </div>
            @if ($approvalActions !== [])
                <form method="POST" action="{{ route('proposals.advance', $proposal) }}" class="form-stack">
                    @csrf
                    <label class="field field--full">
                        <span>Catatan keputusan</span>
                        <textarea name="decision_note" rows="3" placeholder="Opsional, tetapi sangat membantu agar proposal tidak ditebak seperti soal pilihan ganda tanpa kisi-kisi.">{{ old('decision_note') }}</textarea>
                    </label>
                    <div class="field-actions">
                        @foreach ($approvalActions as $action)
                            <button
                                type="submit"
                                name="decision"
                                value="{{ $action['decision'] }}"
                                class="btn {{ $action['decision'] === 'approve' || $action['decision'] === 'resubmit' ? 'btn--secondary' : ($action['decision'] === 'revision' ? 'btn--ghost' : 'btn--primary') }}"
                            >
                                {{ $action['label'] }}
                            </button>
                        @endforeach
                    </div>
                </form>
            @endif
            <div class="list-stack">
                <div class="list-item">
                    <span class="list-item__dot"></span>
                    <p>
                        Pengajuan awal:
                        {{ $proposal->submitted_at?->format('d M Y H:i') ?? 'Belum dikirim ke jalur persetujuan.' }}
                    </p>
                </div>
                <div class="list-item">
                    <span class="list-item__dot"></span>
                    <p>
                        Persetujuan Kaprodi:
                        {{ $proposal->kaprodiApprover?->display_full_name ?? 'Belum ada' }}
                        @if ($proposal->kaprodi_approved_at)
                            &middot; {{ $proposal->kaprodi_approved_at->format('d M Y H:i') }}
                        @endif
                    </p>
                </div>
                <div class="list-item">
                    <span class="list-item__dot"></span>
                    <p>
                        Persetujuan Dekan:
                        {{ $proposal->dekanApprover?->display_full_name ?? 'Belum ada' }}
                        @if ($proposal->dekan_approved_at)
                            &middot; {{ $proposal->dekan_approved_at->format('d M Y H:i') }}
                        @endif
                    </p>
                </div>
                <div class="list-item">
                    <span class="list-item__dot"></span>
                    <p>
                        Penerimaan LPPM:
                        {{ $proposal->lppmReceiver?->display_full_name ?? 'Belum masuk antrean review LPPM.' }}
                        @if ($proposal->lppm_received_at)
                            &middot; {{ $proposal->lppm_received_at->format('d M Y H:i') }}
                        @endif
                    </p>
                </div>
                <div class="list-item">
                    <span class="list-item__dot"></span>
                    <p>
                        Keputusan terakhir:
                        {{ $proposal->lastDecisionMaker?->display_full_name ?? 'Belum ada' }}
                        @if ($proposal->last_decision_type)
                            &middot; {{ ucfirst($proposal->last_decision_type) }}
                        @endif
                        @if ($proposal->last_decision_stage)
                            &middot; tahap {{ ucfirst($proposal->last_decision_stage) }}
                        @endif
                        @if ($proposal->last_decision_at)
                            &middot; {{ $proposal->last_decision_at->format('d M Y H:i') }}
                        @endif
                        @if ($proposal->last_decision_note)
                            &middot; {{ $proposal->last_decision_note }}
                        @endif
                    </p>
                </div>
            </div>
        </article>
    </section>

    <section class="content-grid">
        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Kolaborasi</p>
                    <h2>Anggota dan mitra</h2>
                </div>
            </div>
            <div class="list-stack">
                @forelse ($proposal->members as $member)
                    <div class="list-item">
                        <span class="list-item__dot"></span>
                        <p>{{ $member->user?->display_full_name ?? $member->user?->name }} &middot; {{ ucfirst($member->role) }}</p>
                    </div>
                @empty
                    <p class="empty-copy">Belum ada anggota terdaftar.</p>
                @endforelse
            </div>
            <div class="status-chips">
                @forelse ($proposal->partners as $partner)
                    <span>{{ $partner->name }}</span>
                @empty
                    <span>Belum ada mitra</span>
                @endforelse
            </div>
        </article>

        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Review</p>
                    <h2>Umpan balik reviewer</h2>
                </div>
            </div>
            <div class="review-stack">
                @forelse ($proposal->reviews as $review)
                    <article class="review-card">
                        <strong>{{ $review->reviewer?->display_full_name ?? $review->reviewer?->name }}</strong>
                        <span>Skor: {{ $review->score ?? '-' }} &middot; {{ ucfirst($review->status) }}</span>
                        <p>{{ $review->comment ?: 'Belum ada catatan.' }}</p>
                    </article>
                @empty
                    <p class="empty-copy">Belum ada review.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="content-grid">
        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Laporan</p>
                    <h2>Jejak progres kegiatan</h2>
                </div>
            </div>
            <div class="list-stack">
                @forelse ($proposal->reports as $report)
                    <div class="list-item">
                        <span class="list-item__dot"></span>
                        <p>Progress {{ $report->progress }}% &middot; {{ $report->description ?: 'Belum ada deskripsi progres.' }}</p>
                    </div>
                @empty
                    <p class="empty-copy">Belum ada laporan progres.</p>
                @endforelse
            </div>
        </article>

        <article class="surface-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Dokumen</p>
                    <h2>Jejak administrasi proposal</h2>
                </div>
            </div>
            <div class="list-stack">
                @forelse ($proposal->documents as $document)
                    <div class="list-item">
                        <span class="list-item__dot"></span>
                        <p>{{ $document->file_name }} &middot; {{ $document->document_type }}</p>
                    </div>
                @empty
                    <p class="empty-copy">Belum ada dokumen terunggah.</p>
                @endforelse
            </div>
        </article>
    </section>
@endsection
