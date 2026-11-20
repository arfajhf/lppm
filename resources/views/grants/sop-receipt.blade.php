@extends('layouts.base')
@section('title','Bukti Pencatatan SOP | UMB LPPM')
@section('body')
<main class="landing-shell"><section class="surface-card">
<h1>UMB LPPM — Bukti Pencatatan</h1><h2>SOP {{ $action->step_key }}: {{ $definition['title'] }}</h2>
<p>Referensi sistem: SOP-{{ $action->id }} · {{ $action->created_at->format('d M Y H:i') }}</p>
<p>Subjek: {{ $subject->title ?? $subject->user?->display_full_name }} · Dicatat oleh {{ $action->actor?->display_full_name }}</p>
@foreach($action->fields as $name=>$value)
@if(!is_array($value))<p><strong>{{ $definition['fields'][$name]['label'] ?? $name }}:</strong> {{ $value }}</p>@endif
@endforeach
@foreach($action->fields['checks'] ?? [] as $name=>$value)<p>✓ {{ $definition['checks'][$name] ?? $name }}</p>@endforeach
<h3>Bukti pendukung</h3>
@foreach($action->files as $kind=>$file)<p><a href="{{ route('grant-sop.download',[$action,$kind]) }}">{{ $file['name'] }}</a></p>@endforeach
<p>Bukti ini mencatat proses di sistem. Keabsahan SK, tanda tangan, pengiriman, dan pembayaran merujuk pada dokumen resmi terlampir.</p>
<button class="btn btn--secondary" onclick="window.print()">Cetak bukti</button>
</section></main>
@endsection
