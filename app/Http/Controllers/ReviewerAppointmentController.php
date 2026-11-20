<?php

namespace App\Http\Controllers;

use App\Models\GrantCall;
use App\Models\ReviewerAppointment;
use App\Models\User;
use App\Services\GrantSop;
use App\Services\GrantWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReviewerAppointmentController extends Controller
{
    public function index(Request $r, GrantWorkflow $w)
    {
        $query = ReviewerAppointment::with('user');
        if (! $w->isAdmin($r->user()) && ! $r->user()->hasAnyLppmRole(['rektor', 'dekan', 'wakil_rektor'])) {
            $query->where('user_id', $r->user()->user_id);
        }
        if ($r->user()->hasAnyLppmRole(['dekan']) && ! $w->isAdmin($r->user()) && ! $r->user()->hasAnyLppmRole(['rektor', 'wakil_rektor'])) {
            $query->whereHas('user', fn ($q) => $q->where('faculty', $r->user()->faculty));
        }

        return view('grants.reviewers', ['calls' => GrantCall::where('purpose', 'reviewer')->where('published', true)->get()->filter(fn ($c) => $c->isOpen()), 'appointments' => $query->latest()->get(), 'users' => User::orderBy('name')->get(), 'workflow' => $w]);
    }

    public function store(Request $r, GrantWorkflow $w)
    {
        $v = $r->validate(['grant_call_id' => 'required|exists:grant_calls,id', 'user_id' => 'nullable|exists:users,user_id', 'origin' => ['required', Rule::in(['internal', 'external'])], 'expertise' => 'required|string|max:255', 'institution' => 'required|string|max:255', 'portfolio' => 'required|file|mimes:pdf|max:10240']);
        $call = GrantCall::findOrFail($v['grant_call_id']);
        abort_unless($call->purpose === 'reviewer' && $call->isOpen(), 422, 'Pendaftaran reviewer belum dibuka atau sudah ditutup.');
        $userId = $w->isAdmin($r->user()) ? ($v['user_id'] ?? $r->user()->user_id) : $r->user()->user_id;
        ReviewerAppointment::create(collect($v)->except(['portfolio', 'user_id'])->all() + ['user_id' => $userId, 'portfolio_path' => $r->file('portfolio')->store('reviewers', 'local'), 'history' => [['status' => 'registered', 'actor' => $r->user()->user_id, 'at' => now()->toIso8601String()]]]);

        return back()->with('status', 'Pendaftaran reviewer tersimpan.');
    }

    public function advance(Request $r, ReviewerAppointment $appointment, GrantWorkflow $w)
    {
        $r->validate(['note' => 'required|string|max:10000']);
        DB::transaction(function () use ($r, $appointment, $w) {
            $a = ReviewerAppointment::lockForUpdate()->findOrFail($appointment->id);
            $sop = app(GrantSop::class);
            if ($a->status === 'registered') {
                $allowed = $a->origin === 'internal' ? ($r->user()->hasAnyLppmRole(['dekan', 'wakil_rektor']) && $a->user_id !== $r->user()->user_id) : $w->isAdmin($r->user());
                if ($r->user()->hasAnyLppmRole(['dekan']) && ! $r->user()->hasAnyLppmRole(['wakil_rektor'])) {
                    $allowed = $allowed && trim((string) $r->user()->faculty) !== '' && $r->user()->faculty === $a->user->faculty;
                }
                abort_unless($allowed, 403);
                $sop->assertStage('reviewer', $a);
                $a->recommendation_path = $sop->file('reviewer', $a, $a->origin === 'internal' ? '6.2' : '7.3', 'recommendation');
                $a->status = $a->origin === 'external' ? 'selected' : 'recommended';
            } elseif ($a->status === 'recommended') {
                abort_unless($w->isAdmin($r->user()) && $a->user_id !== $r->user()->user_id, 403);
                $sop->assertStage('reviewer', $a);
                $a->status = 'selected';
            } elseif ($a->status === 'selected') {
                abort_unless($r->user()->hasLppmPermission('lppm.grant.award') && $a->user_id !== $r->user()->user_id, 403);
                $sop->assertStage('reviewer', $a);
                $key = $a->origin === 'internal' ? '6.4' : '7.4';
                $action = $sop->action('reviewer', $a, $key);
                $a->decree_path = $sop->file('reviewer', $a, $key, 'decree');
                $a->decree_number = $action->fields['decree_number'];
                $a->valid_until = $action->fields['valid_until'];
                $a->status = 'appointed';
            } else {
                abort(422);
            }
            $history = $a->history ?? [];
            $history[] = ['status' => $a->status, 'actor' => $r->user()->user_id, 'at' => now()->toIso8601String(), 'note' => $r->input('note')];
            $a->history = $history;
            $a->save();
        });

        return back()->with('status', 'Tahapan penetapan reviewer diperbarui.');
    }

    public function publish(Request $r, ReviewerAppointment $appointment, GrantWorkflow $w)
    {
        abort_unless($w->isAdmin($r->user()) && $appointment->status === 'appointed', 403);
        app(GrantSop::class)->assertStage('reviewer', $appointment, 'appointed');
        $history = $appointment->history ?? [];
        $history[] = ['status' => 'published', 'actor' => $r->user()->user_id, 'at' => now()->toIso8601String(), 'note' => 'Hasil penetapan reviewer diumumkan.'];
        $appointment->update(['published' => true, 'history' => $history]);

        return back()->with('status', 'Hasil penetapan reviewer diumumkan.');
    }

    public function download(Request $r, ReviewerAppointment $appointment, string $kind, GrantWorkflow $w)
    {
        abort_unless($w->isAdmin($r->user()) || $appointment->user_id === $r->user()->user_id || $r->user()->hasAnyLppmRole(['dekan', 'wakil_rektor', 'rektor']), 403);
        if ($r->user()->hasAnyLppmRole(['dekan']) && ! $w->isAdmin($r->user()) && ! $r->user()->hasAnyLppmRole(['rektor', 'wakil_rektor'])) {
            abort_unless(trim((string) $r->user()->faculty) !== '' && $r->user()->faculty === $appointment->user->faculty, 403);
        }
        abort_unless(in_array($kind, ['portfolio', 'recommendation', 'decree']), 404);
        $path = $appointment->{$kind.'_path'};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }
}
