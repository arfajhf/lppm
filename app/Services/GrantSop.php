<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Report;
use App\Models\SopAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GrantSop
{
    public function definitions(string $scope, Model $subject): array
    {
        return array_filter(config('grant_sop'), fn ($d) => $d['scope'] === $scope && (! $d['origin'] || $d['origin'] === $subject->origin) && (! $d['purpose'] || $d['purpose'] === $subject->purpose));
    }

    public function stage(string $scope, Model $subject): string
    {
        return match ($scope) {
            'proposal' => $subject->grant_stage === 'revision' ? 'revision' : ($subject->grant_stage ?? ''),
            'reviewer' => $subject->status,
            'call' => $subject->published ? 'annual' : 'setup',
        };
    }

    public function cycle(string $scope, Model $subject, string $key): int
    {
        $definition = $this->definitions($scope, $subject)[$key] ?? null;
        if ($scope !== 'proposal' || ! $definition) {
            return 0;
        }

        return (int) ($subject->grantRecords()->where('kind', 'resubmit')->where('to_stage', $definition['stage'])->max('id') ?? 0);
    }

    public function action(string $scope, Model $subject, string $key): ?SopAction
    {
        $d = $this->definitions($scope, $subject)[$key] ?? null;
        if (! $d) {
            return null;
        }
        $a = SopAction::where('scope', $scope)->where('subject_id', (string) $subject->getKey())->where('step_key', $key)->where('cycle', $this->cycle($scope, $subject, $key))->latest('id')->first();
        if (! $a) {
            return null;
        }
        foreach ($a->files as $file) {
            if (! Storage::disk('local')->exists($file['path'])) {
                return null;
            }
        }
        foreach ($this->prerequisites($subject, $key, $d) as $dependency) {
            $before = $this->action($scope, $subject, $dependency);
            if (! $before || $before->id >= $a->id) {
                return null;
            }
        }

        return $a;
    }

    private function prerequisites(Model $subject, string $key, array $d): array
    {
        return array_merge($d['after'], $key === '35.3' && $subject->origin === 'internal' ? ['6.3'] : []);
    }

    public function allowed(string $scope, Model $subject, array $d, User $user): bool
    {
        $w = app(GrantWorkflow::class);
        $self = isset($subject->user_id) && $subject->user_id === $user->user_id;
        if ($d['role'] === 'owner') {
            return $self && $user->hasLppmPermission('lppm.proposal.submit');
        }
        if ($self && $scope !== 'call') {
            return false;
        }

        return match ($d['role']) {
            'admin' => $w->isAdmin($user),
            'secretariat' => $user->hasLppmPermission('lppm.grant.socialise'),
            'head' => $user->hasLppmPermission('lppm.grant.archive'),
            'finance' => $user->hasLppmPermission('lppm.grant.disburse'),
            'rektor' => $user->hasLppmPermission('lppm.grant.award'),
            'rector_wr' => $user->hasLppmPermission('lppm.grant.award') || $user->hasAnyLppmRole(['wakil_rektor']),
            'dekan' => $user->hasAnyLppmRole(['dekan']) && trim((string) $user->faculty) !== '' && $user->faculty === $subject->user?->faculty,
            'dean_wr' => $user->hasAnyLppmRole(['wakil_rektor']) || ($user->hasAnyLppmRole(['dekan']) && trim((string) $user->faculty) !== '' && $user->faculty === $subject->user?->faculty),
            'faculty' => $user->hasAnyLppmRole(['dekan', 'kaprodi']) && trim((string) $user->faculty) !== '' && $user->faculty === $subject->user?->faculty && ($user->hasAnyLppmRole(['dekan']) || (trim((string) $user->study_program) !== '' && $user->study_program === $subject->user?->study_program)),
            default => false,
        };
    }

    public function dueAt(string $scope, Model $subject, string $key): ?Carbon
    {
        $d = $this->definitions($scope, $subject)[$key];
        if (! $d['days']) {
            return null;
        }
        if ($scope === 'call' && $d['stage'] === 'annual' && ! $d['after']) {
            return null;
        }
        $start = $subject->created_at;
        if ($scope === 'reviewer') {
            foreach ($subject->history ?? [] as $event) {
                if (($event['status'] ?? '') === $d['stage'] && ! empty($event['at'])) {
                    $start = Carbon::parse($event['at']);
                }
            }
        }
        foreach ($this->prerequisites($subject, $key, $d) as $before) {
            $a = $this->action($scope, $subject, $before);
            if (! $a) {
                return null;
            }
            if ($a->created_at->greaterThan($start)) {
                $start = $a->created_at;
            }
        }
        if ($scope === 'proposal') {
            $entry = $subject->grantRecords()->where('to_stage', $d['stage'])->latest('id')->first();
            if ($entry && $entry->created_at->greaterThan($start)) {
                $start = $entry->created_at;
            }
        }

        return $start->copy()->addDays($d['days'][1]);
    }

    public function assertStage(string $scope, Model $subject, ?string $stage = null): void
    {
        $stage ??= $this->stage($scope, $subject);
        $missing = [];
        foreach ($this->definitions($scope, $subject) as $key => $d) {
            if ($d['stage'] === $stage && $d['gate'] && ! $this->action($scope, $subject, $key)) {
                $missing[] = 'SOP '.$key.': '.$d['title'];
            }
        }
        if ($missing) {
            throw ValidationException::withMessages(['sop' => implode('; ', $missing)]);
        }
    }

    public function file(string $scope, Model $subject, string $key, string $kind): ?string
    {
        return $this->action($scope, $subject, $key)?->files[$kind]['path'] ?? null;
    }

    public function complete(string $scope, Model $subject, string $key, User $actor, array $input, array $uploads = []): SopAction
    {
        $paths = [];
        try {
            return DB::transaction(function () use ($scope, $subject, $key, $actor, $input, $uploads, &$paths) {
                $subject = $subject->newQuery()->whereKey($subject->getKey())->lockForUpdate()->firstOrFail();
                $d = $this->definitions($scope, $subject)[$key] ?? null;
                abort_unless($d, 404);
                abort_unless($this->allowed($scope, $subject, $d, $actor), 403);
                abort_unless($d['stage'] === $this->stage($scope, $subject), 422, 'Langkah bukan pada tahap aktif.');
                foreach ($this->prerequisites($subject, $key, $d) as $before) {
                    if (! $this->action($scope, $subject, $before)) {
                        throw ValidationException::withMessages(['sop' => 'Selesaikan SOP '.$before.' terlebih dahulu.']);
                    }
                }
                $rules = [];
                foreach ($d['fields'] as $name => $f) {
                    $rules['fields.'.$name] = $f['rule'];
                }
                foreach ($d['checks'] as $name => $label) {
                    $rules['checks.'.$name] = 'required|accepted';
                }
                $validated = Validator::make($input, $rules)->validate();
                $fields = $validated['fields'] ?? [];
                if ($key === '5.4') {
                    Validator::make($fields, ['registration_number' => ['required', 'string', 'max:150', Rule::unique('proposals', 'registration_number')->ignore($subject->getKey(), 'proposal_id')]])->validate();
                }
                $fields['checks'] = $validated['checks'] ?? [];
                if (in_array($key, ['18.3', '18.7'], true)) {
                    $expected = $key === '18.3' ? intdiv($subject->grant_amount * 70, 100) : $subject->grant_amount - intdiv($subject->grant_amount * 70, 100);
                    if ((int) $fields['amount'] !== $expected) {
                        throw ValidationException::withMessages(['fields.amount' => 'Nominal harus sesuai tahap pencairan: Rp '.$expected]);
                    }
                }
                if ($key === '18.4' && $fields['reported_at'] < $fields['started_at']) {
                    throw ValidationException::withMessages(['fields.reported_at' => 'Tanggal terakhir tidak boleh sebelum tanggal mulai.']);
                }
                if ($key === '18.4' && ! $subject->grantRecords()->where('kind', 'logbook')->exists()) {
                    throw ValidationException::withMessages(['sop' => 'Logbook belum diisi.']);
                }
                if ($key === '11.2' && ! $subject->grantRecords()->where('id', '>', $this->cycle($scope, $subject, $key))->where('kind', 'assignment')->where('data->review_kind', 'monev')->exists()) {
                    throw ValidationException::withMessages(['sop' => 'Reviewer monev belum ditugaskan.']);
                }
                if ($key === '11.4') {
                    app(GrantWorkflow::class)->assertAssessments($subject, 'monev');
                }
                $files = [];
                if ($key === '5.3') {
                    $endorsement = $this->action('proposal', $subject, '5.2');
                    if (! $endorsement) {
                        throw ValidationException::withMessages(['sop' => 'Pengesahan Dekan SOP 5.2 belum tercatat.']);
                    }
                    $input['existing_documents']['endorsement'] = $endorsement->files['endorsement']['document_id'];
                }
                foreach ($d['documents'] as $kind => $label) {
                    $existing = null;
                    if ($scope === 'proposal' && ! empty($input['existing_documents'][$kind])) {
                        $existing = $subject->documents()->where('document_type', $kind)->find($input['existing_documents'][$kind]);
                    }
                    if ($existing && Storage::disk('local')->exists($existing->file_path)) {
                        if ($kind === 'archive_scan' && Storage::disk('local')->mimeType($existing->file_path) !== 'application/pdf') {
                            throw ValidationException::withMessages(['file' => 'Arsip digital wajib berformat PDF.']);
                        }
                        $files[$kind] = ['path' => $existing->file_path, 'name' => $existing->file_name, 'document_id' => $existing->document_id];

                        continue;
                    }
                    $file = $uploads[$kind] ?? null;
                    Validator::make(['file' => $file], ['file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240'], [], ['file' => $label])->validate();
                    if ($kind === 'archive_scan') {
                        Validator::make(['file' => $file], ['file' => 'mimes:pdf'])->validate();
                    }
                    $path = $file->store('sop/'.$scope.'/'.$subject->getKey(), 'local');
                    abort_unless($path, 500);
                    $paths[] = $path;
                    $files[$kind] = ['path' => $path, 'name' => $file->getClientOriginalName()];
                    if ($scope === 'proposal') {
                        $doc = Document::create(['proposal_id' => $subject->proposal_id, 'uploaded_by' => $actor->user_id, 'file_name' => $file->getClientOriginalName(), 'file_path' => $path, 'document_type' => $kind]);
                        $files[$kind]['document_id'] = $doc->document_id;
                    }
                }
                $a = SopAction::create(['scope' => $scope, 'subject_id' => (string) $subject->getKey(), 'step_key' => $key, 'actor_id' => $actor->user_id, 'fields' => $fields, 'files' => $files, 'cycle' => $this->cycle($scope, $subject, $key), 'due_at' => $this->dueAt($scope, $subject, $key)]);
                if ($scope === 'proposal') {
                    if ($key === '5.4') {
                        $subject->update(['registration_number' => $fields['registration_number']]);
                    }
                    $reportMap = match ($key) {
                        '18.5' => ['progress70' => 'progress_report'], '18.8' => ['final100' => 'final_report', 'finance100' => 'financial_report', 'outputs' => 'output_evidence'], '9.4' => ['revised_final' => 'revised_final_report'],default => []
                    };
                    foreach ($reportMap as $type => $documentKind) {
                        Report::create(['proposal_id' => $subject->proposal_id, 'report_type' => $type, 'progress' => $key === '18.5' ? 70 : 100, 'description' => $fields['achievements'] ?? $fields['outputs'] ?? $fields['response'] ?? $d['title'], 'submitted_at' => now(), 'submitted_by' => $actor->user_id, 'document_id' => $files[$documentKind]['document_id'], 'report_data' => $fields]);
                    }
                    if (in_array($key, ['11.2', '9.2'], true)) {
                        $kind = $key === '11.2' ? 'monev' : 'seminar';
                        foreach ($subject->grantRecords()->where('kind', 'assignment')->where('data->review_kind', $kind)->where('id', '>', $this->cycle($scope, $subject, $key))->get() as $assignment) {
                            $data = $assignment->data;
                            $data['assessment_due_at'] = Carbon::parse($fields[$key === '11.2' ? 'assigned_at' : 'presented_at'])->endOfDay()->addDays(7)->toIso8601String();
                            $data['deadline_source'] = $key === '11.2' ? 'SOP 11.3' : 'SOP 9.3';
                            $assignment->update(['data' => $data]);
                        }
                    }
                    if ($key === '10.5') {
                        $subject->reports()->whereNull('accepted_at')->update(['accepted_at' => now(), 'accepted_by' => $actor->user_id, 'receipt_reference' => $fields['receipt_number']]);
                    }
                }
                if ($scope === 'proposal') {
                    $subject->grantRecords()->create(['actor_id' => $actor->user_id, 'kind' => 'sop_step', 'note' => 'SOP '.$key.': '.$d['title'], 'data' => ['sop_action_id' => $a->id]]);
                }

                return $a;
            });
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            } throw $e;
        }
    }
}
