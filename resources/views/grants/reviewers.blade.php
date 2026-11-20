@extends('layouts.app')
@section('page_title','Penetapan Reviewer')
@section('content')
@if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
<section class="surface-card"><h2>Pendaftaran calon reviewer internal / eksternal</h2>
<form method="POST" enctype="multipart/form-data" action="{{ route('reviewer-appointments.store') }}" class="form-stack">@csrf
<label class="field">Periode pendaftaran<select name="grant_call_id" required><option value="">Pilih periode</option>@foreach($calls as $call)<option value="{{ $call->id }}">{{ $call->title }}</option>@endforeach</select></label>
@if($workflow->isAdmin(auth()->user()))<label class="field">Calon reviewer<select name="user_id">@foreach($users as $u)<option value="{{ $u->user_id }}">{{ $u->display_full_name }}</option>@endforeach</select></label>@endif
<label class="field">Asal<select name="origin"><option value="internal">Internal</option><option value="external">Eksternal</option></select></label>
<label class="field">Bidang keahlian<input name="expertise" required></label><label class="field">Instansi<input name="institution" required></label><label class="field">Portofolio (PDF)<input type="file" name="portfolio" accept=".pdf" required></label><button class="btn btn--primary">Daftarkan</button></form></section>
@foreach($appointments as $a)<section class="surface-card"><h2>{{ $a->user->display_full_name }}</h2><a class="btn btn--primary" href="{{ route('grant-sop.show',['reviewer',$a->id]) }}">Permohonan, rekomendasi, seleksi, dan surat SOP</a><p>{{ $a->origin }} · {{ $a->expertise }} · {{ $a->institution }} · {{ $a->status }}</p>
@foreach(['portfolio','recommendation','decree'] as $kind)@if($a->{$kind.'_path'})<a class="btn btn--ghost" href="{{ route('reviewer-appointments.download',[$a,$kind]) }}">{{ $kind }}</a>@endif @endforeach
@if($a->status==='appointed')<p>Administrasi penetapan: {{ $a->readyForAssignment() ? 'lengkap dan aktif' : 'belum lengkap / tidak aktif' }}.</p>
@if(!$a->user->hasLppmPermission('lppm.review.score'))<p class="alert alert--danger">Hak penilaian belum aktif. Pengelola SSO perlu memberikan permission lppm.review.score sesuai penetapan reviewer; SK tidak mengubah role SSO secara otomatis.</p>@endif
@endif
@if($a->decree_number)<p>SK {{ $a->decree_number }} · Berlaku sampai {{ $a->valid_until->format('d M Y') }}</p>@endif
@if($a->status!=='appointed' && (auth()->user()->hasAnyLppmRole(['dekan','wakil_rektor','rektor']) || $workflow->isAdmin(auth()->user())))
<form method="POST" enctype="multipart/form-data" action="{{ route('reviewer-appointments.advance',$a) }}" class="form-stack">@csrf
<label class="field">Catatan rekomendasi / seleksi / penetapan<textarea name="note" required></textarea></label>
<p>Lengkapi dokumen dan pemeriksaan pada halaman langkah SOP sebelum melanjutkan.</p>
<button class="btn btn--secondary">{{ ['registered'=>'Catat rekomendasi','recommended'=>'Selesaikan seleksi LPPM','selected'=>'Catat penetapan SK Rektor'][$a->status] }}</button></form>@endif
@if($a->status==='appointed' && !$a->published && $workflow->isAdmin(auth()->user()))
<form method="POST" action="{{ route('reviewer-appointments.publish',$a) }}">@csrf<button class="btn btn--secondary">Umumkan hasil penetapan</button></form>
@endif
@foreach($a->history ?? [] as $h)<p>{{ $h['at'] }} · {{ $h['status'] }} · {{ $h['note'] ?? '' }}</p>@endforeach
</section>@endforeach
@endsection
