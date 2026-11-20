<?php

namespace App\Services;

use App\Models\ActivityNotification;
use App\Models\GrantRecord;
use App\Models\Proposal;
use App\Models\ReviewerAppointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class GrantWorkflow
{
    public const STAGES = [
        'draft' => 'Draft', 'endorsement' => 'Pengesahan Dekan', 'upload' => 'Unggah proposal disahkan',
        'validation' => 'Validasi administrasi LPPM', 'review' => 'Review proposal', 'award' => 'Penetapan SK penerima',
        'contract' => 'Kontrak', 'payment70' => 'Pencairan 70%', 'execution' => 'Pelaksanaan dan laporan kemajuan',
        'progress' => 'Laporan kemajuan 70%', 'monev' => 'Monev internal', 'payment30' => 'Pencairan 30%', 'final' => 'Laporan akhir dan anggaran 100%',
        'seminar' => 'Seminar hasil', 'final_revision' => 'Perbaikan laporan final', 'archive' => 'Validasi dan arsip',
        'completed' => 'Selesai', 'rejected' => 'Ditolak', 'revision' => 'Revisi',
    ];

    public const RULES = [
        'draft' => ['endorsement', 'owner', [], 3],
        'endorsement' => ['upload', 'dekan', ['endorsement'], 30],
        'upload' => ['validation', 'owner', ['proposal', 'endorsement'], 3],
        'validation' => ['review', 'admin', [], 7],
        'review' => ['award', 'admin', [], 1],
        'award' => ['contract', 'rektor', ['award_decree'], 1],
        'contract' => ['payment70', 'admin', ['signed_contract'], 1],
        'payment70' => ['execution', 'finance', ['transfer70', 'receipt70'], 90],
        'execution' => ['progress', 'owner', [], 1],
        'progress' => ['monev', 'owner', ['progress_report'], 1],
        'monev' => ['payment30', 'admin', ['monev_report'], 1],
        'payment30' => ['final', 'finance', ['transfer30', 'receipt30'], 12],
        'final' => ['seminar', 'owner', ['final_report', 'financial_report', 'output_evidence'], 7],
        'seminar' => ['final_revision', 'admin', ['seminar_minutes'], 14],
        'final_revision' => ['archive', 'owner', ['revised_final_report'], 7],
        'archive' => ['completed', 'head', ['archive_inventory', 'report_receipt'], 0],
    ];

    public const DOCUMENTS = [
        'endorsement' => 'Halaman pengesahan Dekan', 'proposal' => 'Proposal lengkap', 'award_decree' => 'SK Rektor penerima hibah',
        'signed_contract' => 'Kontrak bertanda tangan', 'transfer70' => 'Bukti transfer 70%', 'receipt70' => 'Kuitansi 70%',
        'progress_report' => 'Laporan kemajuan 70%', 'monev_report' => 'Laporan hasil monev',
        'transfer30' => 'Bukti transfer 30%', 'receipt30' => 'Kuitansi 30%', 'final_report' => 'Laporan akhir',
        'financial_report' => 'Realisasi anggaran 100%', 'output_evidence' => 'Bukti capaian luaran',
        'seminar_minutes' => 'Berita acara seminar', 'revised_final_report' => 'Laporan final diperbaiki',
        'archive_inventory' => 'Inventaris arsip disahkan', 'report_receipt' => 'Tanda terima laporan',
        'reviewer_decree' => 'SK reviewer', 'reviewer_recommendation' => 'Rekomendasi reviewer', 'reviewer_portfolio' => 'Portofolio reviewer',
    ];

    public function isAdmin(User $u): bool
    {
        return $u->hasLppmPermission('lppm.grant.manage');
    }

    public function canAct(Proposal $p, User $u, ?string $role = null): bool
    {
        $role ??= self::RULES[$p->grant_stage][1] ?? '';

        return match ($role) {
            'owner' => $p->user_id === $u->user_id && $u->hasLppmPermission('lppm.proposal.submit'),
            'admin' => $this->isAdmin($u) && $p->user_id !== $u->user_id,
            'head' => $u->hasLppmPermission('lppm.grant.archive') && $p->user_id !== $u->user_id,
            'finance' => $u->hasLppmPermission('lppm.grant.disburse') && $p->user_id !== $u->user_id,
            'rektor' => $u->hasLppmPermission('lppm.grant.award') && $p->user_id !== $u->user_id,
            'dekan' => $u->hasAnyLppmRole(['dekan']) && $p->user_id !== $u->user_id
                && trim((string) $u->faculty) !== '' && $u->faculty === $p->user->faculty,
            default => false,
        };
    }

    public function uploadTypes(Proposal $p, User $u): array
    {
        if (! $p->grant_stage || in_array($p->grant_stage, ['completed', 'rejected'], true)) {
            return [];
        }
        $stage = $p->grant_stage === 'revision' ? $p->revision_stage : $p->grant_stage;
        $types = [];
        if ($p->user_id === $u->user_id && $u->hasLppmPermission('lppm.document.upload')) {
            $types = match ($stage) {
                'draft','endorsement','upload','validation','review' => ['proposal'],
                'contract' => ['signed_contract'],
                'execution','progress','monev' => ['progress_report', 'output_evidence'],
                'final','archive' => ['final_report', 'financial_report', 'output_evidence', 'revised_final_report'],
                'final_revision' => ['revised_final_report', 'output_evidence'],
                default => [],
            };
        }
        if ($this->isAdmin($u)) {
            $types = array_merge($types, match ($stage) {
                'draft','upload' => ['proposal'],
                'endorsement','validation','review' => $p->grant_stage === 'revision' ? ['proposal'] : [],
                'contract' => ['signed_contract'], 'execution','progress' => ['progress_report', 'output_evidence'],
                'monev' => ['monev_report'], 'final' => ['final_report', 'financial_report', 'output_evidence'],
                'seminar' => ['seminar_minutes'], 'final_revision' => ['revised_final_report'], default => [],
            });
        }
        foreach (['endorsement' => ['dekan', ['endorsement']], 'award' => ['rektor', ['award_decree']], 'payment70' => ['finance', ['transfer70', 'receipt70']], 'payment30' => ['finance', ['transfer30', 'receipt30']], 'archive' => ['head', ['archive_inventory', 'report_receipt']]] as $target => $rule) {
            if ($p->grant_stage === $target && $this->canAct($p, $u, $rule[0])) {
                $types = array_merge($types, $rule[1]);
            }
        }

        $labels = self::DOCUMENTS;
        foreach (app(GrantSop::class)->definitions('proposal', $p) as $d) {
            $labels = array_merge($labels, $d['documents']);
            if ($d['stage'] === $p->grant_stage && app(GrantSop::class)->allowed('proposal', $p, $d, $u)) {
                $types = array_merge($types, array_keys($d['documents']));
            }
        }

        if ($p->user_id === $u->user_id && $p->grant_stage !== 'revision' && in_array($p->grant_stage, ['endorsement', 'validation', 'review', 'monev', 'archive'], true)) {
            $types = [];
        }

        return array_intersect_key($labels, array_flip(array_unique($types)));
    }

    public function advance(Proposal $proposal, User $user, array $input): void
    {
        DB::transaction(function () use ($proposal, $user, $input) {
            $p = Proposal::whereKey($proposal->getKey())->lockForUpdate()->firstOrFail();
            $stage = $p->grant_stage;
            abort_unless($stage !== null, 422);
            $decision = $input['decision'];
            $note = trim($input['note'] ?? '');
            if ($stage === 'revision') {
                abort_unless($decision === 'resubmit' && $this->canAct($p, $user, 'owner'), 403);
                $lastRevision = $p->grantRecords()->where('kind', 'revision')->max('id') ?? 0;
                $this->require($p->grantRecords()->where('id', '>', $lastRevision)->whereIn('kind', ['edit', 'document', 'followup'])->exists(), 'Perbaiki proposal, dokumen, atau catat tindak lanjut sebelum mengirim ulang.');
                $next = $p->revision_stage;
                $p->revision_stage = null;
            } else {
                $rule = self::RULES[$stage] ?? null;
                abort_unless($rule && $this->canAct($p, $user), 403);
                if (in_array($decision, ['revision', 'reject'], true)) {
                    abort_unless(in_array($stage, ['endorsement', 'validation', 'review', 'monev', 'archive'], true), 422);
                    $this->require($note !== '', 'Catatan keputusan wajib diisi.');
                    $next = $decision === 'reject' ? 'rejected' : 'revision';
                    $p->revision_stage = $stage;
                } else {
                    abort_unless($decision === 'advance', 422);
                    app(GrantSop::class)->assertStage('proposal', $p, $stage);
                    $next = $rule[0];
                    foreach ($rule[2] as $kind) {
                        $this->require($p->documents()->where('document_type', $kind)->exists(), 'Dokumen wajib: '.self::DOCUMENTS[$kind]);
                    }
                    if (in_array($stage, ['draft', 'upload'], true)) {
                        $this->require($p->grantCall && $p->grantCall->purpose === 'grant' && $p->grantCall->isOpen(), 'Periode pengajuan hibah sudah ditutup atau belum dibuka.');
                    }
                    if ($stage === 'validation') {
                        $this->require(! empty($input['checklist_confirmed']), 'Konfirmasi kelengkapan proposal, tim, pengesahan, dan kesesuaian roadmap.');
                    }
                    if (in_array($stage, ['review', 'monev', 'seminar'], true)) {
                        $this->assertAssessments($p, ['review' => 'proposal', 'monev' => 'monev', 'seminar' => 'seminar'][$stage]);
                        $cycle = $p->grantRecords()->where('kind', 'resubmit')->where('to_stage', $stage)->max('id') ?? 0;
                        if ($p->grantRecords()->where('kind', 'assessment')->where('id', '>', $cycle)->get()->contains(fn ($a) => in_array($a->data['recommendation'] ?? '', ['revision', 'reject'], true))) {
                            $this->require($note !== '', 'Catat alasan keputusan dan penyelesaian rekomendasi reviewer sebelum melanjutkan.');
                        }
                    }
                    if ($stage === 'award') {
                        $amount = filter_var($input['grant_amount'] ?? null, FILTER_VALIDATE_INT);
                        $this->require($amount && $amount > 0, 'Nilai hibah wajib berupa rupiah bulat positif.');
                        $p->grant_amount = $amount;
                    }
                    if ($stage === 'execution') {
                        $this->require($p->grantRecords()->where('kind', 'logbook')->exists(), 'Logbook kegiatan wajib diisi.');
                    }
                    if ($stage === 'archive') {
                        $this->require(! empty($input['checklist_confirmed']), 'Konfirmasi laporan, luaran, keuangan, dan berkas fisik telah diperiksa.');
                    }
                }
            }
            $p->grant_stage = $next;
            $days = ['endorsement' => 3, 'upload' => 30, 'validation' => 3, 'award' => 1, 'contract' => 1, 'payment70' => 1, 'execution' => 90, 'progress' => 1, 'monev' => 1, 'payment30' => 1, 'final' => 12, 'final_revision' => 14];
            $p->stage_due_at = isset($days[$next]) ? now()->addDays($days[$next]) : null;
            $p->status = match ($next) {
                'draft' => 'draft','endorsement' => 'pending_dekan','upload','validation' => 'submitted','review','award' => 'review','revision' => 'revision','rejected' => 'rejected','completed' => 'completed',default => 'approved'
            };
            if ($stage === 'draft') {
                $p->submitted_at = now();
            }
            if ($stage === 'endorsement' && $decision === 'advance') {
                $p->dekan_approved_by = $user->user_id;
                $p->dekan_approved_at = now();
            }
            $p->save();
            $data = [];
            if (in_array($stage, ['payment70', 'payment30']) && $decision === 'advance') {
                $this->require($p->grant_amount > 0, 'Nilai hibah belum ditetapkan.');
                $data = ['percentage' => $stage === 'payment70' ? 70 : 30, 'amount' => $stage === 'payment70' ? intdiv($p->grant_amount * 70, 100) : $p->grant_amount - intdiv($p->grant_amount * 70, 100)];
            }
            GrantRecord::create(['proposal_id' => $p->proposal_id, 'actor_id' => $user->user_id, 'kind' => $decision, 'from_stage' => $stage, 'to_stage' => $next, 'note' => $note, 'data' => $data + ['deadline_source' => isset($days[$next]) ? ($next === 'endorsement' ? 'SOP 5.2' : ($next === 'upload' ? 'SOP 5.3' : ($next === 'validation' ? 'SOP 5.4' : ($next === 'final_revision' ? 'SOP 9.4' : 'SOP 18')))) : 'Tidak ada durasi tahap tunggal; gunakan tenggat langkah SOP / jadwal resmi']]);
            ActivityNotification::create(['user_id' => $p->user_id, 'message' => 'Hibah '.$p->title.': '.self::STAGES[$next].($note ? '. '.$note : ''), 'channel' => 'hibah']);
            User::where('user_id', '!=', $p->user_id)->get()->filter(fn ($u) => $this->canAct($p, $u))->each(function ($u) use ($p, $next) {
                ActivityNotification::create(['user_id' => $u->user_id, 'message' => 'Perlu tindak lanjut hibah '.$p->title.': '.self::STAGES[$next], 'channel' => 'hibah']);
            });
        });
    }

    public function assertAssessments(Proposal $p, string $kind): void
    {
        $stage = ['proposal' => 'review', 'monev' => 'monev', 'seminar' => 'seminar'][$kind];
        $cycleStart = $p->grantRecords()->where('kind', 'resubmit')->where('to_stage', $stage)->max('id') ?? 0;
        $assignments = $p->grantRecords()->where('kind', 'assignment')->where('id', '>', $cycleStart)->get()->filter(fn ($r) => ($r->data['review_kind'] ?? '') === $kind);
        $this->require($assignments->isNotEmpty(), 'Reviewer belum ditugaskan.');
        foreach ($assignments as $a) {
            $this->require(ReviewerAppointment::where('user_id', $a->data['reviewer_id'])->get()->contains(fn ($appointment) => $appointment->readyForAssignment()), 'Penetapan dan administrasi reviewer tidak aktif / belum lengkap.');
            $review = $p->grantRecords()->where('kind', 'assessment')->where('data->assignment_id', $a->id)->latest('id')->first();
            $this->require($review && ! empty($review->data['criteria_reference']) && ! empty($review->data['strengths']) && ! empty($review->data['improvements']) && ! empty($review->data['recommendation']) && ! empty($review->data['assessment_document_id']), 'Seluruh reviewer wajib menyelesaikan borang, temuan, dan rekomendasi penilaian.');
            $form = $p->documents()->where('document_id', $review->data['assessment_document_id'])->where('uploaded_by', $a->data['reviewer_id'])->where('document_type', 'assessment_form')->first();
            if ($kind === 'seminar') {
                $this->require(! empty($review->data['presentation_confirmed']), 'Reviewer harus mengonfirmasi pemaparan hasil seminar.');
            }
            $this->require($form && Storage::disk('local')->exists($form->file_path), 'Borang penilaian resmi reviewer belum tersimpan.');
        }
    }

    private function require(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['workflow' => $message]);
        }
    }
}
