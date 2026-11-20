@extends('layouts.app')

@section('title', 'Buat Proposal | UMB LPPM')
@section('eyebrow', 'Proposal Baru')
@section('page_title', 'Formulir Proposal')

@section('content')
    <section class="surface-card form-card form-card--wide">
        <div class="form-card__header">
            <p class="eyebrow">Input Proposal</p>
            <h2>Susun pengajuan dengan alur yang siap ditinjau</h2>
            <p>Form awal ini sengaja dibuat ringkas tapi sudah mengikuti kolom inti pada blueprint LPPM.</p>
        </div>

        <div class="helper-card">
            Proposal hibah disimpan sebagai draft. Ajukan pengesahan Dekan melalui halaman alur hibah.
        </div>

        @if ($errors->any())
            <div class="alert alert--danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('proposals.store') }}" class="form-grid">
            @csrf
            <label class="field field--full">
                <span>Judul Proposal</span>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Pemetaan Dampak Pengabdian Berbasis Data" required>
            </label>

            <label class="field">
                <span>Jenis Kegiatan</span>
                <select name="type" required>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected(old('type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </label>

            <label class="field">
                <span>Skema</span>
                <input type="text" name="scheme" value="{{ old('scheme') }}" placeholder="Skema kompetitif / kolaboratif" required>
            </label>

            <label class="field">
                <span>Periode hibah (wajib untuk jenis Hibah)</span>
                <select name="grant_call_id"><option value="">Pilih periode</option>
                @foreach (\App\Models\GrantCall::where('purpose','grant')->where('published',true)->get()->filter(fn($c)=>$c->isOpen()) as $call)
                    <option value="{{ $call->id }}" @selected(old('grant_call_id') == $call->id)>{{ $call->title }}</option>
                @endforeach
                </select>
            </label>
            <label class="field">
                <span>Tahun</span>
                <input type="number" name="year" value="{{ old('year', now()->year) }}" min="2020" max="2100" required>
            </label>

            <label class="field field--full">
                <span>Ringkasan</span>
                <textarea name="summary" rows="5" placeholder="Jelaskan tujuan, manfaat, dan ruang lingkup proposal.">{{ old('summary') }}</textarea>
            </label>

            <div class="field-actions field--full">
                <a href="{{ route('proposals.index') }}" class="btn btn--ghost">Kembali</a>
                <button type="submit" class="btn btn--primary">Simpan Proposal</button>
            </div>
        </form>
    </section>
@endsection
