<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesLppmProposalAccess;
use App\Models\ActivityNotification;
use App\Models\GrantCall;
use App\Models\Proposal;
use App\Support\LppmWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Mengelola daftar, detail, dan pembuatan proposal di workspace LPPM.
 */
class ProposalController extends Controller
{
    use AuthorizesLppmProposalAccess;

    /**
     * Menampilkan proposal yang dapat diakses user beserta ringkasan statusnya.
     */
    public function index()
    {
        $user = auth()->user();
        $module = LppmWorkspace::module($user, 'proposals');
        $proposalQuery = $this->scopeProposalVisibility(
            Proposal::with(['user', 'members.user', 'reviews'])
        );

        $statusSummary = (clone $proposalQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $proposals = $proposalQuery->latest()->paginate(9);

        return view('proposals.index', compact('proposals', 'module', 'statusSummary'));
    }

    /**
     * Menyiapkan referensi awal untuk formulir pembuatan proposal.
     */
    public function create()
    {
        $types = [
            'Penelitian Mandiri',
            'Penelitian Hibah',
            'Pengabdian Mandiri',
            'Pengabdian Hibah',
        ];
        $submissionTarget = $this->submissionTargetLabel(auth()->user());

        return view('proposals.create', compact('types', 'submissionTarget'));
    }

    /**
     * Menyimpan proposal baru dan menentukan status awal sesuai permission submit.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()->hasLppmPermission('lppm.proposal.create'), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['Penelitian Mandiri', 'Penelitian Hibah', 'Pengabdian Mandiri', 'Pengabdian Hibah'])],
            'scheme' => ['required', 'string', 'max:100'],
            'year' => ['required', 'digits:4'],
            'summary' => ['nullable', 'string'],
            'grant_call_id' => ['nullable', 'exists:grant_calls,id'],
        ]);

        $hibah = in_array($validated['type'], ['Penelitian Hibah', 'Pengabdian Hibah'], true);
        if ($hibah) {
            $call = GrantCall::find($validated['grant_call_id'] ?? null);
            if (! $call || $call->purpose !== 'grant' || ! $call->isOpen()) {
                throw ValidationException::withMessages(['grant_call_id' => 'Pilih periode hibah yang sedang dibuka.']);
            }
        } else {
            unset($validated['grant_call_id']);
        }
        $proposal = Proposal::create($validated + [
            'grant_stage' => $hibah ? 'draft' : null,
            'user_id' => Auth::id(),
            'status' => $hibah ? 'draft' : $this->initialProposalStatus($request->user()),
            'submitted_at' => ! $hibah && $request->user()->hasLppmPermission('lppm.proposal.submit') ? now() : null,
        ]);

        return redirect()->route($hibah ? 'grants.show' : 'proposals.show', $proposal)->with('status', 'Proposal berhasil disimpan.');
    }

    /**
     * Menampilkan detail proposal lengkap dengan dokumen, review, dan mitra terkait.
     */
    public function show(Proposal $proposal)
    {
        $this->ensureUserCanAccessProposal($proposal);
        if ($proposal->grant_stage) {
            return redirect()->route('grants.show', $proposal);
        }

        $user = auth()->user();
        $proposal->load([
            'user',
            'members.user',
            'documents',
            'reviews.reviewer',
            'reports',
            'partners',
            'kaprodiApprover',
            'dekanApprover',
            'lppmReceiver',
            'lastDecisionMaker',
        ]);
        $module = LppmWorkspace::module($user, 'proposals');
        $guidance = LppmWorkspace::proposalGuidance($user, $proposal);
        $approvalActions = $this->approvalActionsFor($proposal, $user);

        return view('proposals.show', compact('proposal', 'module', 'guidance', 'approvalActions'));
    }

    public function advance(Request $request, Proposal $proposal)
    {
        abort_if($proposal->grant_stage !== null || in_array($proposal->type, ['Penelitian Hibah', 'Pengabdian Hibah'], true), 422, 'Gunakan alur hibah.');
        $this->ensureUserCanAccessProposal($proposal);

        $user = $request->user();
        $actions = collect($this->approvalActionsFor($proposal, $user))->keyBy('decision');
        $decision = strtolower((string) $request->input('decision'));
        $action = $actions->get($decision);
        abort_unless($action !== null, 403);

        $request->validate([
            'decision' => ['required', 'string'],
            'decision_note' => ['nullable', 'string'],
        ]);

        $note = trim((string) $request->input('decision_note', ''));
        $stage = $this->approvalStageFor($proposal);
        $payload = match ($decision) {
            'approve' => $this->approvalPayloadFor($proposal, $user, $stage),
            'revision' => $this->decisionPayloadFor($user, $stage, Proposal::STATUS_REVISION, 'revision', $note),
            'reject' => $this->decisionPayloadFor($user, $stage, Proposal::STATUS_REJECTED, 'reject', $note),
            'resubmit' => $this->resubmissionPayloadFor($proposal, $user, $note),
            default => [],
        };

        $proposal->fill($payload)->save();
        $this->notifyProposalOwner($proposal, $user, $action, $note);

        return redirect()
            ->route('proposals.show', $proposal)
            ->with('status', $action['success_message']);
    }

    public function edit(Proposal $proposal)
    {
        abort_unless($proposal->user_id === auth()->id() && auth()->user()->hasLppmPermission('lppm.proposal.update') && in_array($proposal->grant_stage ?? $proposal->status, ['draft', 'revision']), 403);

        return view('grants.edit', compact('proposal'));
    }

    public function update(Request $request, Proposal $proposal)
    {
        abort_unless($proposal->user_id === auth()->id() && $request->user()->hasLppmPermission('lppm.proposal.update') && in_array($proposal->grant_stage ?? $proposal->status, ['draft', 'revision']), 403);
        $data = $request->validate(['title' => 'required|string|max:255', 'summary' => 'nullable|string', 'member_ids' => 'nullable|array', 'member_ids.*' => 'distinct|exists:users,user_id']);
        DB::transaction(function () use ($proposal, $data, $request) {
            $proposal = Proposal::whereKey($proposal->getKey())->lockForUpdate()->firstOrFail();
            abort_unless(in_array($proposal->grant_stage ?? $proposal->status, ['draft', 'revision']), 403);
            $proposal->update(collect($data)->only(['title', 'summary'])->all());
            $proposal->members()->delete();
            foreach ($data['member_ids'] ?? [] as $id) {
                if ($id !== $proposal->user_id) {
                    $proposal->members()->create(['user_id' => $id, 'role' => 'anggota']);
                }
            }
            if ($proposal->grant_stage) {
                $proposal->grantRecords()->create(['actor_id' => $request->user()->user_id, 'kind' => 'edit', 'data' => ['title' => $proposal->title, 'summary' => $proposal->summary, 'member_ids' => $data['member_ids'] ?? []]]);
            }
        });

        return redirect()->route('proposals.show', $proposal)->with('status', 'Perubahan proposal disimpan.');
    }

    private function initialProposalStatus($user): string
    {
        if (! $user->hasLppmPermission('lppm.proposal.submit')) {
            return Proposal::STATUS_DRAFT;
        }

        if ($user->hasAnyLppmRole(['dekan'])) {
            return Proposal::STATUS_SUBMITTED;
        }

        if ($user->hasAnyLppmRole(['kaprodi'])) {
            return Proposal::STATUS_PENDING_DEKAN;
        }

        return Proposal::STATUS_PENDING_KAPRODI;
    }

    private function submissionTargetLabel($user): string
    {
        return match ($this->initialProposalStatus($user)) {
            Proposal::STATUS_PENDING_KAPRODI => 'Kaprodi untuk persetujuan awal',
            Proposal::STATUS_PENDING_DEKAN => 'Dekan untuk persetujuan fakultas',
            Proposal::STATUS_SUBMITTED => 'LPPM untuk verifikasi dan tindak lanjut review',
            default => 'Ruang draft proposal Anda',
        };
    }

    private function approvalStageFor(Proposal $proposal): ?string
    {
        return match ($proposal->status) {
            Proposal::STATUS_PENDING_KAPRODI => 'kaprodi',
            Proposal::STATUS_PENDING_DEKAN => 'dekan',
            Proposal::STATUS_SUBMITTED => 'lppm',
            default => null,
        };
    }

    private function approvalActionsFor(Proposal $proposal, $user): array
    {
        $actions = [];
        $stage = $this->approvalStageFor($proposal);

        if ($stage === 'kaprodi' && $user->hasAnyLppmRole(['kaprodi']) && $this->proposalMatchesStudyProgram($proposal, $user)) {
            $actions = [
                ['decision' => 'approve', 'label' => 'Setujui dan teruskan ke Dekan', 'success_message' => 'Proposal telah diteruskan ke Dekan.'],
                ['decision' => 'revision', 'label' => 'Kembalikan untuk revisi', 'success_message' => 'Proposal telah dikembalikan untuk revisi oleh Kaprodi.'],
                ['decision' => 'reject', 'label' => 'Tolak proposal', 'success_message' => 'Proposal telah ditolak oleh Kaprodi.'],
            ];
        }

        if ($stage === 'dekan' && $user->hasAnyLppmRole(['dekan']) && $this->proposalMatchesFaculty($proposal, $user)) {
            $actions = [
                ['decision' => 'approve', 'label' => 'Setujui dan teruskan ke LPPM', 'success_message' => 'Proposal telah diteruskan ke LPPM.'],
                ['decision' => 'revision', 'label' => 'Kembalikan untuk revisi', 'success_message' => 'Proposal telah dikembalikan untuk revisi oleh Dekan.'],
                ['decision' => 'reject', 'label' => 'Tolak proposal', 'success_message' => 'Proposal telah ditolak oleh Dekan.'],
            ];
        }

        if ($stage === 'lppm' && $user->hasAnyLppmRole(['lppm_admin', 'super_admin', 'admin_lppm', 'admin'])) {
            $actions = [
                ['decision' => 'approve', 'label' => 'Terima ke antrean review LPPM', 'success_message' => 'Proposal telah masuk ke antrean review LPPM.'],
                ['decision' => 'revision', 'label' => 'Kembalikan untuk revisi', 'success_message' => 'Proposal telah dikembalikan untuk revisi oleh LPPM.'],
                ['decision' => 'reject', 'label' => 'Tolak proposal', 'success_message' => 'Proposal telah ditolak oleh LPPM.'],
            ];
        }

        if ($proposal->user_id === $user->user_id && $proposal->status === Proposal::STATUS_REVISION) {
            $actions[] = ['decision' => 'resubmit', 'label' => 'Kirim ulang proposal', 'success_message' => 'Proposal telah dikirim ulang ke jalur persetujuan.'];
        }

        return $actions;
    }

    private function approvalPayloadFor(Proposal $proposal, $user, ?string $stage): array
    {
        $payload = [
            'last_decision_stage' => $stage,
            'last_decision_type' => 'approve',
            'last_decision_by' => $user->user_id,
            'last_decision_at' => now(),
        ];

        return match ($stage) {
            'kaprodi' => $payload + [
                'status' => Proposal::STATUS_PENDING_DEKAN,
                'kaprodi_approved_by' => $user->user_id,
                'kaprodi_approved_at' => now(),
            ],
            'dekan' => $payload + [
                'status' => Proposal::STATUS_SUBMITTED,
                'dekan_approved_by' => $user->user_id,
                'dekan_approved_at' => now(),
            ],
            'lppm' => $payload + [
                'status' => Proposal::STATUS_REVIEW,
                'lppm_received_by' => $user->user_id,
                'lppm_received_at' => now(),
            ],
            default => $payload,
        };
    }

    private function decisionPayloadFor($user, ?string $stage, string $status, string $decisionType, string $note): array
    {
        return [
            'status' => $status,
            'last_decision_stage' => $stage,
            'last_decision_type' => $decisionType,
            'last_decision_note' => $note !== '' ? $note : null,
            'last_decision_by' => $user->user_id,
            'last_decision_at' => now(),
        ];
    }

    private function resubmissionPayloadFor(Proposal $proposal, $user, string $note): array
    {
        return [
            'status' => $this->initialProposalStatus($user),
            'submitted_at' => $user->hasLppmPermission('lppm.proposal.submit') ? now() : null,
            'kaprodi_approved_by' => null,
            'kaprodi_approved_at' => null,
            'dekan_approved_by' => null,
            'dekan_approved_at' => null,
            'lppm_received_by' => null,
            'lppm_received_at' => null,
            'last_decision_stage' => null,
            'last_decision_type' => 'resubmit',
            'last_decision_note' => $note !== '' ? $note : null,
            'last_decision_by' => $user->user_id,
            'last_decision_at' => now(),
        ];
    }

    private function proposalMatchesStudyProgram(Proposal $proposal, $user): bool
    {
        $proposalStudyProgram = strtolower(trim((string) $proposal->user?->study_program));
        $approverStudyProgram = strtolower(trim((string) $user->study_program));

        return $proposalStudyProgram !== '' && $proposalStudyProgram === $approverStudyProgram;
    }

    private function proposalMatchesFaculty(Proposal $proposal, $user): bool
    {
        $proposalFaculty = strtolower(trim((string) $proposal->user?->faculty));
        $approverFaculty = strtolower(trim((string) $user->faculty));

        return $proposalFaculty !== '' && $proposalFaculty === $approverFaculty;
    }

    private function notifyProposalOwner(Proposal $proposal, $actor, array $action, string $note): void
    {
        if (! $proposal->relationLoaded('user')) {
            $proposal->load('user');
        }

        if (! $proposal->user || $proposal->user->user_id === $actor->user_id) {
            return;
        }

        ActivityNotification::create([
            'user_id' => $proposal->user->user_id,
            'message' => trim($action['label'].' untuk proposal "'.$proposal->title.'"'.($note !== '' ? '. Catatan: '.$note : '.')),
            'channel' => 'approval',
        ]);
    }
}
