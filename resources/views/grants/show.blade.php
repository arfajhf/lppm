@extends('layouts.app')
@section('page_title',$proposal->title)
@section('content')
@php $uploadTypes=$workflow->uploadTypes($proposal,auth()->user()); $stages=\App\Services\GrantWorkflow::STAGES; $documents=\App\Services\GrantWorkflow::DOCUMENTS; foreach(config('grant_sop') as $definition) { $documents=array_merge($documents,$definition['documents']); } @endphp
@if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
<section class="surface-card">
<p>{{ $proposal->grantCall?->title }} · {{ $proposal->type }} · {{ $proposal->user->display_full_name }}</p>
<h2>{{ $stages[$proposal->grant_stage] ?? $proposal->grant_stage }}</h2>
<a class="btn btn--primary" href="{{ route('grant-sop.show',['proposal',$proposal->proposal_id]) }}">Lengkapi langkah dan bukti SOP tahap ini</a>
<p>{{ $proposal->summary }}</p>
<p>Tim: {{ $proposal->members->map(fn($m)=>$m->user?->display_full_name)->filter()->implode(', ') ?: 'Belum ada anggota tambahan.' }}</p><p>Nilai hibah: Rp {{ number_format($proposal->grant_amount ?? 0,0,',','.') }}</p>
@if($proposal->stage_due_at)<p>Tenggat tahap: {{ $proposal->stage_due_at->format('d M Y') }} {{ $proposal->stage_due_at->isPast() ? '(melewati tenggat)' : '' }}</p>@endif
@if($proposal->user_id===auth()->id() && in_array($proposal->grant_stage,['draft','revision']))<a class="btn btn--secondary" href="{{ route('proposals.edit',$proposal) }}">Edit proposal dan tim</a>@endif
@if($workflow->canAct($proposal,auth()->user()) || ($proposal->grant_stage==='revision' && $workflow->canAct($proposal,auth()->user(),'owner')))
<form method="POST" action="{{ route('grants.advance',$proposal) }}" class="form-stack">@csrf
<label class="field">Catatan keputusan<textarea name="note">{{ old('note') }}</textarea></label>
@if($proposal->grant_stage==='award')<label class="field">Nilai hibah (rupiah bulat)<input type="number" name="grant_amount" min="1" max="1000000000000" required></label>@endif
@if(in_array($proposal->grant_stage,['validation','archive']))<label><input type="checkbox" name="checklist_confirmed" value="1" required> Saya sudah memeriksa kelengkapan {{ $proposal->grant_stage==='validation' ? 'proposal, tim, pengesahan, dan kesesuaian roadmap' : 'laporan, luaran, anggaran, serta berkas fisik' }}.</label>@endif
@if($proposal->grant_stage==='revision')<button name="decision" value="resubmit" class="btn btn--primary">Kirim ulang ke tahap pemeriksaan</button>
@else
<p>Dokumen wajib tahap ini: {{ collect(\App\Services\GrantWorkflow::RULES[$proposal->grant_stage][2] ?? [])->map(fn($d)=>$documents[$d])->implode(', ') ?: 'Lihat persyaratan dan penugasan terkait.' }}</p>
<button name="decision" value="advance" class="btn btn--primary">Selesaikan tahap dan lanjutkan</button>
@if(in_array($proposal->grant_stage,['endorsement','validation','review','monev','archive']))<button name="decision" value="revision" class="btn btn--ghost">Minta revisi</button><button name="decision" value="reject" class="btn btn--ghost">Tolak</button>@endif
@endif
</form>@endif
</section>
<section class="surface-card"><h2>Dokumen dan versi</h2>
@foreach($proposal->documents()->latest()->get() as $document)<p><a href="{{ route('documents.download',$document) }}">{{ $documents[$document->document_type] ?? $document->document_type }} — {{ $document->file_name }}</a> · {{ $document->created_at->format('d M Y H:i') }}</p>@endforeach
@if($uploadTypes !== [])
<form method="POST" enctype="multipart/form-data" action="{{ route('grants.documents',$proposal) }}" class="form-stack">@csrf
<label class="field">Jenis dokumen<select name="document_type">@foreach($uploadTypes as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
<label class="field">Berkas PDF / JPG / PNG, maksimal 10 MB<input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required></label><button class="btn btn--secondary">Unggah versi baru</button></form>@endif
</section>
@if(!in_array($proposal->grant_stage,['completed','rejected']))
    @if($proposal->user_id===auth()->id())
        @if($proposal->grant_stage==='execution')
        <section class="surface-card"><h2>Catatan harian kegiatan</h2>
            <form method="POST" action="{{ route('grants.records',$proposal) }}" class="form-stack">@csrf
                <input type="hidden" name="kind" value="logbook">
                <label class="field">Tanggal kegiatan<input type="date" name="event_date" max="{{ today()->toDateString() }}" required></label>
                <label class="field">Kegiatan, hasil, dan kendala<textarea name="note" rows="4" required></textarea></label>
                <button class="btn btn--secondary">Simpan logbook</button>
            </form>
        </section>
        @endif
        <section class="surface-card"><h2>Tindak lanjut dan perbaikan</h2>
            <form method="POST" action="{{ route('grants.records',$proposal) }}" class="form-stack">@csrf
                <input type="hidden" name="kind" value="followup">
                <label class="field">Catatan tindak lanjut<textarea name="note" rows="3" required></textarea></label>
                <button class="btn btn--secondary">Simpan tindak lanjut</button>
            </form>
        </section>
    @endif
    @if($workflow->isAdmin(auth()->user()) && in_array($proposal->grant_stage,['review','monev','seminar']))
        <section class="surface-card"><h2>Penugasan reviewer dan jadwal</h2>
            <p>Pilih reviewer dengan SK yang masih berlaku. Ketua dan anggota tim tidak dapat menilai kegiatannya sendiri.</p>
            <form method="POST" action="{{ route('grants.records',$proposal) }}" class="form-stack">@csrf
                <input type="hidden" name="kind" value="assignment">
                <input type="hidden" name="review_kind" value="{{ ['review'=>'proposal','monev'=>'monev','seminar'=>'seminar'][$proposal->grant_stage] }}">
                <label class="field">Reviewer<select name="reviewer_id" required><option value="">Pilih reviewer</option>
                    @foreach($users->filter(fn($u)=>$u->hasLppmPermission('lppm.review.score') && $u->user_id!==$proposal->user_id) as $user)
                        <option value="{{ $user->user_id }}">{{ $user->display_full_name }}</option>
                    @endforeach
                </select></label>
                <label class="field">Jadwal pelaksanaan / batas penilaian<input type="date" name="event_date" required></label>
                <label class="field">Surat tugas, lokasi, serta panduan penilaian<textarea name="note" rows="4" required></textarea></label>
                <button class="btn btn--secondary">Tugaskan reviewer</button>
            </form>
        </section>
    @endif
    @if(auth()->user()->hasLppmPermission('lppm.review.score'))
        @php $cycleStart=$records->where('kind','resubmit')->where('to_stage',$proposal->grant_stage)->max('id') ?? 0; @endphp
        @foreach($records->where('kind','assignment')->filter(fn($r)=>$r->id>$cycleStart && ($r->data['reviewer_id'] ?? '')===auth()->id()) as $assignment)
            @if(($assignment->data['review_kind'] ?? '') === (['review'=>'proposal','monev'=>'monev','seminar'=>'seminar'][$proposal->grant_stage] ?? null))
            <section class="surface-card"><h2>Penilaian {{ $assignment->data['review_kind'] }}</h2>
                <p>{{ $assignment->note }} · Jadwal: {{ $assignment->data['event_date'] ?? '-' }}</p>
@if(isset($assignment->data['assessment_due_at']))<p>Batas penilaian: {{ \Illuminate\Support\Carbon::parse($assignment->data['assessment_due_at'])->format('d M Y') }} · {{ $assignment->data['deadline_source'] }}</p>@endif
                <form method="POST" enctype="multipart/form-data" action="{{ route('grants.records',$proposal) }}" class="form-stack">@csrf
                    <input type="hidden" name="kind" value="assessment"><input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
                    <label class="field">Skor (0–100)<input type="number" name="score" min="0" max="100" step="0.01" required></label>
                    <label class="field">Hasil penilaian, rekomendasi, dan perbaikan<textarea name="note" rows="5" required></textarea></label>
                    <label class="field">Referensi kriteria / borang resmi<input name="criteria_reference" required maxlength="2000"></label>
                    <label class="field">Kekuatan dan capaian<textarea name="strengths" required maxlength="5000"></textarea></label>
                    <label class="field">Kekurangan dan perbaikan<textarea name="improvements" required maxlength="5000"></textarea></label>
                    <label class="field">Rekomendasi<select name="recommendation"><option value="approve">Layak / diterima</option><option value="revision">Perlu perbaikan</option><option value="reject">Tidak layak</option></select></label>
                    <label class="field">Borang penilaian resmi yang sudah diisi (PDF)<input type="file" name="assessment_form" accept=".pdf" required></label>
                    @if($proposal->grant_stage==='seminar')<label><input type="checkbox" name="presentation_confirmed" value="1" required> Saya mengonfirmasi pemaparan hasil oleh peneliti telah dilaksanakan.</label>@endif
                    <button class="btn btn--secondary">Kirim penilaian</button>
                </form>
            </section>
            @endif
        @endforeach
    @endif
    @if(auth()->user()->hasAnyLppmRole(['kaprodi','dekan']) && $proposal->user_id!==auth()->id())
        <section class="surface-card"><h2>Verifikasi kesesuaian roadmap</h2>
            <form method="POST" action="{{ route('grants.records',$proposal) }}" class="form-stack">@csrf
                <input type="hidden" name="kind" value="roadmap">
                <label class="field">Kesesuaian dengan roadmap dan rekomendasi fakultas/prodi<textarea name="note" rows="4" required></textarea></label>
                <button class="btn btn--secondary">Simpan verifikasi</button>
            </form>
        </section>
    @endif
@endif
<section class="surface-card"><h2>Riwayat kegiatan dan keputusan</h2>
@foreach($records as $record)<article class="helper-card"><strong>{{ $record->actor?->display_full_name }} · {{ $record->kind }} · {{ $record->created_at->format('d M Y H:i') }}</strong><p>{{ $record->note }}</p>@if($record->kind==='document' && isset($record->data['document_id']))<p>Bukti unggah: {{ $record->data['document_id'] }} · {{ $record->created_at }}</p>@endif
@if($record->to_stage)<p>{{ $stages[$record->from_stage] ?? $record->from_stage }} → {{ $stages[$record->to_stage] ?? $record->to_stage }}</p>@endif
@if(isset($record->data['amount']))<p>Pencairan {{ $record->data['percentage'] }}%: Rp {{ number_format($record->data['amount'],0,',','.') }}</p>@endif
@if(isset($record->data['score']))<p>Skor {{ $record->data['score'] }}</p>@endif
@if(isset($record->data['event_date']))<p>Jadwal {{ $record->data['event_date'] }}</p>@endif
</article>@endforeach
</section>
@endsection
