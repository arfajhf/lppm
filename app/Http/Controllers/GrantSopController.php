<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesLppmProposalAccess;
use App\Models\GrantCall;
use App\Models\Proposal;
use App\Models\ReviewerAppointment;
use App\Models\SopAction;
use App\Services\GrantSop;
use App\Services\GrantWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GrantSopController extends Controller
{
    use AuthorizesLppmProposalAccess;

    public function subject(string $scope, string $id)
    {
        return match ($scope) {
            'proposal' => Proposal::findOrFail($id),'reviewer' => ReviewerAppointment::findOrFail($id),'call' => GrantCall::findOrFail($id),default => abort(404)
        };
    }

    private function authorizeSubject(string $scope, $subject): void
    {
        $u = auth()->user();
        abort_unless($u && $u->hasLppmPermission('lppm.proposal.view'), 403);
        if ($scope === 'proposal') {
            $this->ensureUserCanAccessProposal($subject);

            return;
        }
        $w = app(GrantWorkflow::class);
        if ($scope === 'call') {
            abort_unless($w->isAdmin($u) || $u->hasLppmPermission('lppm.grant.socialise') || $u->hasAnyLppmRole(['dekan', 'kaprodi', 'rektor', 'wakil_rektor', 'kepala_lppm']), 403);

            return;
        }
        $allowed = $subject->user_id === $u->user_id || $w->isAdmin($u) || $u->hasAnyLppmRole(['rektor', 'wakil_rektor']);
        if ($u->hasAnyLppmRole(['dekan']) && trim((string) $u->faculty) !== '' && $u->faculty === $subject->user->faculty) {
            $allowed = true;
        }
        abort_unless($allowed, 403);
    }

    public function show(string $scope, string $id, GrantSop $sop)
    {
        $subject = $this->subject($scope, $id);
        $this->authorizeSubject($scope, $subject);
        $definitions = $sop->definitions($scope, $subject);
        $actions = SopAction::with('actor')->where('scope', $scope)->where('subject_id', $id)->latest('id')->get();

        return view('grants.sop', compact('scope', 'subject', 'sop', 'definitions', 'actions'));
    }

    public function store(Request $r, string $scope, string $id, string $step, GrantSop $sop)
    {
        $subject = $this->subject($scope, $id);
        $this->authorizeSubject($scope, $subject);
        $sop->complete($scope, $subject, $step, $r->user(), $r->all(), $r->file('files', []));

        return back()->with('status', 'Langkah SOP '.$step.' dan bukti pendukung disimpan.');
    }

    public function download(SopAction $action, string $kind)
    {
        $subject = $this->subject($action->scope, $action->subject_id);
        $this->authorizeSubject($action->scope, $subject);
        $file = $action->files[$kind] ?? null;
        abort_unless($file && Storage::disk('local')->exists($file['path']), 404);

        return Storage::disk('local')->download($file['path'], $file['name']);
    }

    public function receipt(SopAction $action)
    {
        $subject = $this->subject($action->scope, $action->subject_id);
        $this->authorizeSubject($action->scope, $subject);
        $definition = app(GrantSop::class)->definitions($action->scope, $subject)[$action->step_key];

        return view('grants.sop-receipt', compact('action', 'subject', 'definition'));
    }

    public function publishCall(Request $r, GrantCall $call, GrantSop $sop, GrantWorkflow $w)
    {
        abort_unless($w->isAdmin($r->user()), 403);
        DB::transaction(function () use ($call, $sop) {
            $call = GrantCall::whereKey($call->id)->lockForUpdate()->firstOrFail();
            abort_if($call->published, 422);
            $sop->assertStage('call', $call, 'setup');
            $key = $call->purpose === 'grant' ? '5.1' : '35.1';
            $call->update(['published' => true, 'announcement_path' => $sop->file('call', $call, $key, 'announcement')]);
        });

        return back()->with('status', 'Periode diterbitkan setelah kelengkapan SOP diperiksa.');
    }

    public function export(GrantCall $call)
    {
        $u = auth()->user();
        abort_unless($u && ($u->hasLppmPermission('lppm.grant.manage') || $u->hasAnyLppmRole(['dekan', 'kaprodi', 'rektor', 'wakil_rektor', 'kepala_lppm'])), 403);
        $query = $this->scopeProposalVisibility(Proposal::with(['user', 'grantRecords'])->where('grant_call_id', $call->id));

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Judul', 'Pengusul', 'Fakultas', 'Prodi', 'Tahun', 'Skema', 'Tahap', 'Nilai hibah', 'SK penerima', 'Kontrak', 'Surat tugas monev', 'Luaran'], ',', '"', '');
            $query->chunkById(100, function ($proposals) use ($out) {
                foreach ($proposals as $p) {
                    $sop = app(GrantSop::class);
                    $values = [$p->proposal_id, $p->title, $p->user->display_full_name, $p->user->faculty, $p->user->study_program, $p->year, $p->scheme, $p->grant_stage, $p->grant_amount, $sop->action('proposal', $p, '18.1')?->fields['decree_number'] ?? '', $sop->action('proposal', $p, '18.2')?->fields['contract_number'] ?? '', $sop->action('proposal', $p, '11.2')?->fields['reference'] ?? '', $sop->action('proposal', $p, '18.8')?->fields['outputs'] ?? ''];
                    fputcsv($out, array_map(fn ($v) => preg_match('/^\s*[=+\-@]|^[\t\r\n]/', (string) $v) ? "'".$v : $v, $values), ',', '"', '');
                }
            }, 'proposal_id');
            fclose($out);
        }, 'rekap-hibah-'.$call->id.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
