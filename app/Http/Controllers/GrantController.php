<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesLppmProposalAccess;
use App\Models\ActivityNotification;
use App\Models\Document;
use App\Models\GrantCall;
use App\Models\GrantRecord;
use App\Models\Proposal;
use App\Models\ReviewerAppointment;
use App\Models\User;
use App\Services\GrantSop;
use App\Services\GrantWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GrantController extends Controller
{
    use AuthorizesLppmProposalAccess;

    public function index(Request $r, GrantWorkflow $w)
    {
        return view('grants.index', ['calls' => GrantCall::when(! $w->isAdmin($r->user()) && ! $r->user()->hasLppmPermission('lppm.grant.socialise'), fn ($q) => $q->where('published', true))->latest()->get(), 'proposals' => $this->scopeProposalVisibility(Proposal::with('user')->whereNotNull('grant_stage'))->latest()->paginate(15)]);
    }

    public function announcements()
    {
        return view('grants.announcements', ['calls' => GrantCall::where('published', true)->latest()->get(), 'reviewers' => ReviewerAppointment::with('user')->where('published', true)->where('status', 'appointed')->whereDate('valid_until', '>=', today())->get()]);
    }

    public function reviewerAnnouncement(ReviewerAppointment $appointment, string $kind)
    {
        abort_unless($appointment->published && $appointment->status === 'appointed', 404);
        abort_unless(in_array($kind, ['decree', 'result_announcement'], true), 404);
        $path = $kind === 'decree' ? $appointment->decree_path : app(GrantSop::class)->file('reviewer', $appointment, '35.4', 'result_announcement');
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function storeCall(Request $r, GrantWorkflow $w)
    {
        abort_unless($w->isAdmin($r->user()), 403);
        $data = $r->validate(['title' => 'required|string|max:255', 'guidelines' => 'required|string', 'opens_at' => 'required|date', 'closes_at' => 'required|date|after_or_equal:opens_at', 'published' => 'sometimes|boolean', 'schedule_exception' => 'nullable|string|max:2000', 'purpose' => ['required', Rule::in(['grant', 'reviewer'])], 'announcement' => 'nullable|file|mimes:pdf|max:10240']);
        $duration = (int) Carbon::parse($data['opens_at'])->diffInDays(Carbon::parse($data['closes_at'])) + 1;
        if (($data['purpose'] === 'grant' && $duration !== 30) || ($data['purpose'] === 'reviewer' && ($duration < 7 || $duration > 14))) {
            if (empty($data['schedule_exception'])) {
                throw ValidationException::withMessages(['schedule_exception' => 'Jadwal berbeda dari acuan SOP (hibah 30 hari; pendaftaran reviewer 7–14 hari). Catat dasar perubahan jadwal resmi.']);
            }
        }
        unset($data['announcement']);
        $data['announcement_path'] = $r->hasFile('announcement') ? $r->file('announcement')->store('grant-announcements', 'local') : null;
        $data['published'] = false;
        GrantCall::create($data);

        return back()->with('status', 'Draft periode disimpan. Lengkapi langkah SOP sebelum diterbitkan.');
    }

    public function announcement(Request $r, GrantCall $call, GrantWorkflow $w)
    {
        abort_unless($call->published || ($r->user() && $w->isAdmin($r->user())), 403);
        abort_unless($call->announcement_path && Storage::disk('local')->exists($call->announcement_path), 404);

        return Storage::disk('local')->download($call->announcement_path);
    }

    public function show(Proposal $proposal, GrantWorkflow $workflow)
    {
        $this->ensureUserCanAccessProposal($proposal);
        abort_unless($proposal->grant_stage, 404);

        return view('grants.show', compact('proposal', 'workflow') + ['records' => $proposal->grantRecords()->with('actor')->latest()->get(), 'users' => User::orderBy('name')->get()]);
    }

    public function enroll(Request $r, Proposal $proposal, GrantWorkflow $w)
    {
        $this->ensureUserCanAccessProposal($proposal);
        abort_unless(($proposal->user_id === $r->user()->user_id || $w->isAdmin($r->user())) && ! $proposal->grant_stage && in_array($proposal->type, ['Penelitian Hibah', 'Pengabdian Hibah'], true), 403);
        $v = $r->validate(['grant_call_id' => 'required|exists:grant_calls,id']);
        $call = GrantCall::findOrFail($v['grant_call_id']);
        abort_unless($call->purpose === 'grant' && $call->isOpen(), 422);
        DB::transaction(function () use ($proposal, $r, $call) {
            $old = $proposal->status;
            $proposal->update(['grant_stage' => 'draft', 'grant_call_id' => $call->id, 'status' => 'draft']);
            $proposal->grantRecords()->create(['actor_id' => $r->user()->user_id, 'kind' => 'enroll', 'to_stage' => 'draft', 'note' => 'Proposal lama dimasukkan ke alur SOP hibah. Berkas dan riwayat lama dipertahankan.', 'data' => ['previous_status' => $old]]);
        });

        return redirect()->route('grants.show', $proposal);
    }

    public function advance(Request $r, Proposal $proposal, GrantWorkflow $w)
    {
        $this->ensureUserCanAccessProposal($proposal);
        $data = $r->validate(['decision' => ['required', Rule::in(['advance', 'revision', 'reject', 'resubmit'])], 'note' => 'nullable|string|max:10000', 'grant_amount' => 'nullable|integer|min:1|max:1000000000000', 'checklist_confirmed' => 'sometimes|accepted']);
        $w->advance($proposal, $r->user(), $data);

        return back()->with('status', 'Tahapan hibah diperbarui.');
    }

    public function upload(Request $r, Proposal $proposal, GrantWorkflow $w)
    {
        return DB::transaction(fn () => $this->uploadLocked($r, Proposal::whereKey($proposal->getKey())->lockForUpdate()->firstOrFail(), $w));
    }

    private function uploadLocked(Request $r, Proposal $proposal, GrantWorkflow $w)
    {
        $this->ensureUserCanAccessProposal($proposal);
        $data = $r->validate(['document_type' => ['required', Rule::in(array_keys($w->uploadTypes($proposal, $r->user())))], 'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240']);
        $kind = $data['document_type'];
        if ($kind === 'archive_scan') {
            $r->validate(['file' => 'required|file|mimes:pdf|max:10240']);
        }
        abort_unless(array_key_exists($kind, $w->uploadTypes($proposal, $r->user())), 403);
        abort_unless($proposal->grant_stage, 422);
        $path = $r->file('file')->store('grants/'.$proposal->proposal_id, 'local');
        abort_unless($path, 500);
        try {
            DB::transaction(function () use ($proposal, $r, $kind, $path) {
                $d = Document::create(['proposal_id' => $proposal->proposal_id, 'file_name' => $r->file('file')->getClientOriginalName(), 'file_path' => $path, 'document_type' => $kind, 'uploaded_by' => $r->user()->user_id]);
                GrantRecord::create(['proposal_id' => $proposal->proposal_id, 'actor_id' => $r->user()->user_id, 'kind' => 'document', 'data' => ['document_id' => $d->document_id, 'document_type' => $kind]]);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return back()->with('status', 'Dokumen tersimpan sebagai versi baru.');
    }

    public function download(Document $document)
    {
        $this->ensureUserCanAccessProposal($document->proposal);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function record(Request $r, Proposal $proposal, GrantWorkflow $w)
    {
        return DB::transaction(fn () => $this->recordLocked($r, Proposal::whereKey($proposal->getKey())->lockForUpdate()->firstOrFail(), $w));
    }

    private function recordLocked(Request $r, Proposal $proposal, GrantWorkflow $w)
    {
        $this->ensureUserCanAccessProposal($proposal);
        $data = $r->validate(['kind' => ['required', Rule::in(['logbook', 'assignment', 'assessment', 'schedule', 'roadmap', 'followup'])], 'note' => 'required|string|max:10000', 'reviewer_id' => 'nullable|exists:users,user_id', 'review_kind' => ['nullable', Rule::in(['proposal', 'monev', 'seminar'])], 'assignment_id' => 'nullable|integer', 'score' => 'nullable|numeric|between:0,100', 'event_date' => 'nullable|date', 'criteria_reference' => 'nullable|string|max:2000', 'strengths' => 'nullable|string|max:5000', 'improvements' => 'nullable|string|max:5000', 'recommendation' => ['nullable', Rule::in(['approve', 'revision', 'reject'])], 'presentation_confirmed' => 'sometimes|accepted']);
        abort_unless($proposal->grant_stage, 422);
        $kind = $data['kind'];
        abort_if(in_array($proposal->grant_stage, ['completed', 'rejected']), 422);
        if ($kind === 'logbook') {
            $r->validate(['event_date' => 'required|date|before_or_equal:today']);
            abort_unless($proposal->grant_stage === 'execution' && $proposal->user_id === $r->user()->user_id, 403);
        }
        if ($kind === 'followup') {
            abort_unless($proposal->user_id === $r->user()->user_id || $w->isAdmin($r->user()), 403);
        }
        if ($kind === 'roadmap') {
            if ($r->user()->hasAnyLppmRole(['kaprodi']) && ! $r->user()->hasAnyLppmRole(['dekan'])) {
                abort_unless(trim((string) $r->user()->study_program) !== '' && $r->user()->study_program === $proposal->user->study_program, 403);
            }
            abort_unless($r->user()->hasAnyLppmRole(['kaprodi', 'dekan']) && trim((string) $r->user()->faculty) !== '' && $r->user()->faculty === $proposal->user->faculty && $proposal->user_id !== $r->user()->user_id, 403);
        }
        if (in_array($kind, ['assignment', 'schedule'])) {
            abort_unless($w->isAdmin($r->user()), 403);
        }
        if ($kind === 'schedule') {
            $r->validate(['event_date' => 'required|date']);
        }
        if ($kind === 'assignment') {
            if ($proposal->grant_stage === 'monev' && ! app(GrantSop::class)->action('proposal', $proposal, '11.1')) {
                throw ValidationException::withMessages(['sop' => 'Jadwal monev SOP 11.1 belum dicatat.']);
            }
            if ($proposal->grant_stage === 'seminar' && ! app(GrantSop::class)->action('proposal', $proposal, '9.1')) {
                throw ValidationException::withMessages(['sop' => 'Jadwal seminar SOP 9.1 belum dicatat.']);
            }
            $r->validate(['reviewer_id' => 'required|exists:users,user_id', 'review_kind' => 'required', 'event_date' => 'required|date']);
            abort_unless($proposal->grant_stage === ['proposal' => 'review', 'monev' => 'monev', 'seminar' => 'seminar'][$data['review_kind']], 422);
            $cycleStart = $proposal->grantRecords()->where('kind', 'resubmit')->where('to_stage', $proposal->grant_stage)->max('id') ?? 0;
            abort_if($proposal->grantRecords()->where('kind', 'assignment')->where('id', '>', $cycleStart)->get()->contains(fn ($a) => ($a->data['reviewer_id'] ?? '') === $data['reviewer_id'] && ($a->data['review_kind'] ?? '') === $data['review_kind']), 422, 'Reviewer sudah ditugaskan pada putaran ini.');
            $reviewer = User::findOrFail($data['reviewer_id']);
            abort_unless($reviewer->hasLppmPermission('lppm.review.score') && $reviewer->user_id !== $proposal->user_id && ! $proposal->members()->where('user_id', $reviewer->user_id)->exists(), 422);
            abort_unless(ReviewerAppointment::where('user_id', $reviewer->user_id)->get()->contains(fn ($appointment) => $appointment->readyForAssignment()), 422, 'Reviewer harus memiliki SK aktif dan administrasi penetapan lengkap (termasuk pengumuman serta penyampaian daftar untuk reviewer internal).');
        }
        if ($kind === 'assessment') {
            abort_unless($r->user()->hasLppmPermission('lppm.review.score'), 403);
            $r->validate(['assignment_id' => 'required|integer', 'score' => 'required|numeric|between:0,100', 'criteria_reference' => 'required|string|max:2000', 'strengths' => 'required|string|max:5000', 'improvements' => 'required|string|max:5000', 'recommendation' => ['required', Rule::in(['approve', 'revision', 'reject'])], 'assessment_form' => 'required|file|mimes:pdf|max:10240']);
            $a = $proposal->grantRecords()->where('kind', 'assignment')->findOrFail($data['assignment_id']);
            $cycleStart = $proposal->grantRecords()->where('kind', 'resubmit')->where('to_stage', $proposal->grant_stage)->max('id') ?? 0;
            abort_unless($a->id > $cycleStart, 422, 'Penugasan berasal dari putaran sebelum revisi.');
            abort_unless(($a->data['reviewer_id'] ?? null) === $r->user()->user_id && $r->user()->hasLppmPermission('lppm.review.score'), 403);
            abort_unless($proposal->grant_stage === ['proposal' => 'review', 'monev' => 'monev', 'seminar' => 'seminar'][$a->data['review_kind']], 422);
        }
        if ($kind === 'assessment') {
            if ($proposal->grant_stage === 'monev' && ! app(GrantSop::class)->action('proposal', $proposal, '11.2')) {
                throw ValidationException::withMessages(['sop' => 'Surat tugas monev SOP 11.2 belum dicatat.']);
            }
            if ($proposal->grant_stage === 'seminar') {
                $r->validate(['presentation_confirmed' => 'required|accepted']);
            }
            if ($proposal->grant_stage === 'seminar' && ! app(GrantSop::class)->action('proposal', $proposal, '9.2')) {
                throw ValidationException::withMessages(['sop' => 'Bukti pemaparan hasil SOP 9.2 belum dicatat.']);
            }
            $form = $r->file('assessment_form');
            $path = $form->store('grants/'.$proposal->proposal_id, 'local');
            abort_unless($path, 500);
            $document = Document::create(['proposal_id' => $proposal->proposal_id, 'uploaded_by' => $r->user()->user_id, 'file_name' => $form->getClientOriginalName(), 'file_path' => $path, 'document_type' => 'assessment_form']);
            $data['assessment_document_id'] = $document->document_id;
        }
        GrantRecord::create(['proposal_id' => $proposal->proposal_id, 'actor_id' => $r->user()->user_id, 'kind' => $kind, 'note' => $data['note'], 'data' => collect($data)->except(['kind', 'note'])->all()]);
        if ($kind === 'assignment') {
            ActivityNotification::create(['user_id' => $data['reviewer_id'], 'message' => 'Penugasan '.$data['review_kind'].' untuk '.$proposal->title.'. Jadwal: '.$data['event_date'], 'channel' => 'hibah']);
        }

        return back()->with('status', 'Catatan hibah disimpan.');
    }
}
