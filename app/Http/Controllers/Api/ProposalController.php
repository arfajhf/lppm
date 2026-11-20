<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesLppmProposalAccess;
use App\Http\Controllers\Controller;
use App\Models\GrantCall;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProposalController extends Controller
{
    use AuthorizesLppmProposalAccess;

    public function index(Request $r)
    {
        abort_unless($r->user()->hasLppmPermission('lppm.proposal.view'), 403);

        return response()->json($this->scopeProposalVisibility(Proposal::with('reviews'))->latest()->paginate(10));
    }

    public function store(Request $r)
    {
        abort_unless($r->user()->hasLppmPermission('lppm.proposal.create'), 403);
        $v = $r->validate(['title' => 'required|string|max:255', 'type' => ['required', Rule::in(['Penelitian Mandiri', 'Penelitian Hibah', 'Pengabdian Mandiri', 'Pengabdian Hibah'])], 'scheme' => 'required|string|max:100', 'year' => 'required|digits:4', 'summary' => 'nullable|string', 'grant_call_id' => 'nullable|exists:grant_calls,id', 'user_id' => 'prohibited', 'status' => 'prohibited']);
        $hibah = in_array($v['type'], ['Penelitian Hibah', 'Pengabdian Hibah']);
        if ($hibah) {
            $c = GrantCall::find($v['grant_call_id'] ?? null);
            if (! $c || $c->purpose !== 'grant' || ! $c->isOpen()) {
                throw ValidationException::withMessages(['grant_call_id' => 'Pilih periode hibah yang sedang dibuka.']);
            }
        } else {
            unset($v['grant_call_id']);
        }

        return response()->json(Proposal::create($v + ['user_id' => $r->user()->user_id, 'status' => 'draft', 'grant_stage' => $hibah ? 'draft' : null]), 201);
    }
}
