@extends('layouts.app')
@section('page_title','Hibah Internal')
@section('content')
@if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
<section class="surface-card">
<h2>Periode penerimaan penelitian dan PkM</h2><a class="btn btn--ghost" href="{{ route('reviewer-appointments.index') }}">Penetapan reviewer</a>
<a class="btn btn--primary" href="{{ route('proposals.create') }}">Buat proposal</a>
@foreach($calls as $call)
<article class="helper-card"><h3>{{ $call->title }}</h3>
@if(auth()->user()->hasLppmPermission('lppm.grant.manage') || auth()->user()->hasLppmPermission('lppm.grant.socialise') || auth()->user()->hasAnyLppmRole(['kepala_lppm','rektor','wakil_rektor','dekan','kaprodi']))<a class="btn btn--ghost" href="{{ route('grant-sop.show',['call',$call->id]) }}">Persiapan, tindak lanjut, dan rekap SOP</a>@endif<p>{{ $call->guidelines }}</p>@if($call->announcement_path)<a href="{{ route('grants.announcement',$call) }}">Surat pengumuman dan panduan</a>@endif<p>{{ $call->opens_at->format('d M Y') }} – {{ $call->closes_at->format('d M Y') }} · {{ $call->isOpen() ? 'Dibuka' : 'Ditutup / belum dibuka' }}</p></article>
@endforeach
</section>
<section class="surface-card"><h2>Hibah yang perlu ditindaklanjuti</h2>
@forelse($proposals as $proposal)
<article class="helper-card"><a href="{{ route('grants.show',$proposal) }}">{{ $proposal->title }}</a><p>{{ $proposal->user->display_full_name }} · {{ \App\Services\GrantWorkflow::STAGES[$proposal->grant_stage] ?? $proposal->grant_stage }}</p></article>
@empty<p>Belum ada hibah dalam lingkup akses Anda.</p>@endforelse
{{ $proposals->links() }}</section>
@if(auth()->user()->hasLppmPermission('lppm.grant.manage'))
<section class="surface-card"><h2>Buka periode hibah</h2>
<form method="POST" action="{{ route('grants.calls.store') }}" enctype="multipart/form-data" class="form-stack">@csrf
<label class="field">Jenis penerimaan<select name="purpose"><option value="grant">Proposal hibah</option><option value="reviewer">Calon reviewer</option></select></label><label class="field">Surat pengumuman dan panduan (PDF)<input type="file" name="announcement" accept=".pdf"></label><label class="field">Nama periode<input name="title" required maxlength="255" value="{{ old('title') }}"></label>
<label class="field">Panduan, persyaratan, dan pengumuman<textarea name="guidelines" required>{{ old('guidelines') }}</textarea></label>
<label class="field">Mulai penerimaan<input type="date" name="opens_at" required value="{{ old('opens_at') }}"></label>
<label class="field">Batas penerimaan<input type="date" name="closes_at" required value="{{ old('closes_at') }}"></label>
<label class="field">Dasar perubahan jadwal resmi (jika berbeda dari acuan SOP)<textarea name="schedule_exception" maxlength="2000"></textarea></label><p>Periode disimpan sebagai draft. Lengkapi langkah persiapan SOP lalu terbitkan.</p>
<button class="btn btn--primary">Simpan periode</button></form></section>
@endif
@endsection
