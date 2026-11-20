@extends('layouts.app')
@section('page_title','Kelengkapan SOP Hibah')
@section('content')
<section class="surface-card"><h2>{{ $subject->title ?? $subject->user?->display_full_name }}</h2>
<p>Setiap langkah mencatat pelaksana, tanggal, hasil pemeriksaan, dan bukti. Unggah dokumen resmi yang benar-benar sudah disahkan atau dikirim. Sistem tidak mengirim surat ke instansi luar.</p>
@if($scope==='proposal')<a class="btn btn--ghost" href="{{ route('grants.show',$subject) }}">Kembali ke alur hibah</a>@endif
@if($scope==='reviewer')<a class="btn btn--ghost" href="{{ route('reviewer-appointments.index') }}">Kembali ke penetapan reviewer</a>@endif
@if($scope==='call' && !$subject->published && auth()->user()->hasLppmPermission('lppm.grant.manage'))
<form method="POST" action="{{ route('grants.calls.publish',$subject) }}">@csrf<button class="btn btn--primary">Terbitkan setelah langkah persiapan lengkap</button></form>
@endif
@if($scope==='call' && $subject->purpose==='grant' && (auth()->user()->hasLppmPermission('lppm.grant.manage') || auth()->user()->hasAnyLppmRole(['dekan','kaprodi','rektor','wakil_rektor','kepala_lppm'])))<a class="btn btn--ghost" href="{{ route('grants.calls.export',$subject) }}">Unduh rekap usulan, kontrak, dan luaran</a>@endif
</section>
@if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
@foreach($definitions as $key=>$definition)
@php $done=$sop->action($scope,$subject,$key); $active=$definition['stage']===$sop->stage($scope,$subject); $due=$sop->dueAt($scope,$subject,$key); @endphp
<section class="surface-card">
<h2>SOP {{ $key }} — {{ $definition['title'] }}</h2>
<p>Output: {{ $definition['output'] }} · {{ $done ? 'Tercatat' : ($active ? 'Belum lengkap' : 'Tahap belum aktif / sudah berlalu') }}</p>
<p>Acuan waktu: {{ $definition['days'] ? ($definition['days'][0]===$definition['days'][1] ? $definition['days'][1] : implode('–',$definition['days'])).' hari' : 'Berkala / mengikuti kalender resmi' }} · Halaman PDF {{ $definition['page'] }}
@if($due && $active) · Batas pencatatan: {{ $due->format('d M Y') }}@endif
</p>
@if($active && $sop->allowed($scope,$subject,$definition,auth()->user()))
<form method="POST" enctype="multipart/form-data" action="{{ route('grant-sop.store',[$scope,$subject->getKey(),$key]) }}" class="form-stack">@csrf
@foreach($definition['fields'] as $name=>$field)
<label class="field">{{ $field['label'] }}
@if(str_contains($field['rule'],'in:maintenance'))
<select name="fields[{{ $name }}]"><option value="maintenance">Pemeliharaan</option><option value="physical_disposal">Pencatatan pemusnahan fisik yang telah disahkan</option></select>
@elseif($name==='output_type')<select name="fields[{{ $name }}]"><option value="journal">Jurnal</option><option value="hki">Kekayaan intelektual</option><option value="book">Buku</option><option value="other">Lainnya</option></select>
@elseif(str_contains($field['rule'],'date'))<input type="date" name="fields[{{ $name }}]" required>
@elseif(str_contains($field['rule'],'integer'))<input type="number" name="fields[{{ $name }}]" min="0" step="1" required>
@else<textarea name="fields[{{ $name }}]" rows="2" @required(!str_contains($field['rule'],'nullable')) maxlength="2000"></textarea>@endif
</label>
@endforeach
@foreach($definition['checks'] as $name=>$label)<label><input type="checkbox" name="checks[{{ $name }}]" value="1" required> {{ $label }}</label>@endforeach
@foreach($definition['documents'] as $kind=>$label)
<label class="field">{{ $label }} (PDF/JPG/PNG; maksimal 10 MB)<input type="file" name="files[{{ $kind }}]" accept=".pdf,.jpg,.jpeg,.png" @required($scope!=='proposal')></label>
@if($scope==='proposal')<label class="field">Atau gunakan dokumen yang sudah diunggah<select name="existing_documents[{{ $kind }}]"><option value="">Pilih dokumen {{ $label }}</option>@foreach($subject->documents()->where('document_type',$kind)->latest()->get() as $doc)<option value="{{ $doc->document_id }}">{{ $doc->file_name }} · {{ $doc->created_at }}</option>@endforeach</select></label>@endif
@endforeach
<button class="btn btn--secondary">Simpan hasil langkah SOP {{ $key }}</button>
</form>
@endif
@if($done)<p>Dicatat {{ $done->created_at->format('d M Y H:i') }} oleh {{ $done->actor?->display_full_name }}.</p>@endif
</section>
@endforeach
<section class="surface-card"><h2>Bukti dan riwayat pencatatan</h2>
@foreach($actions as $action)<article class="helper-card"><a class="btn btn--ghost" href="{{ route('grant-sop.receipt',$action) }}">Bukti pencatatan / cetak</a><h3>SOP {{ $action->step_key }} · {{ $action->actor?->display_full_name }} · {{ $action->created_at }}</h3>
@foreach($action->fields as $name=>$value)@if(!is_array($value))<p>{{ $definitions[$action->step_key]['fields'][$name]['label'] ?? $name }}: {{ $value }}</p>@endif @endforeach
@foreach($action->files as $kind=>$file)<p><a href="{{ route('grant-sop.download',[$action,$kind]) }}">{{ $file['name'] }}</a></p>@endforeach
</article>@endforeach
</section>
@endsection
