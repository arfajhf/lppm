@extends('layouts.app')
@section('page_title','Perbaiki Proposal')
@section('content')
<section class="surface-card">
@if($errors->any())<div class="alert alert--danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('proposals.update',$proposal) }}" class="form-stack">@csrf @method('PUT')
<label class="field">Judul<input name="title" value="{{ old('title',$proposal->title) }}" required></label>
<label class="field">Ringkasan<textarea name="summary" rows="6">{{ old('summary',$proposal->summary) }}</textarea></label>
<label class="field">Anggota tim<select name="member_ids[]" multiple>
@foreach(\App\Models\User::where('user_id','!=',$proposal->user_id)->orderBy('name')->get() as $member)
<option value="{{ $member->user_id }}" @selected(in_array($member->user_id,old('member_ids',$proposal->members->pluck('user_id')->all())))>{{ $member->display_full_name }}</option>
@endforeach
</select></label><button class="btn btn--primary">Simpan perubahan</button></form></section>
@endsection
