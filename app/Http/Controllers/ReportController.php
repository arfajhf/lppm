<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesLppmProposalAccess;
use App\Models\Report;
use App\Support\LppmWorkspace;

class ReportController extends Controller
{
    use AuthorizesLppmProposalAccess;

    public function index()
    {
        $module = LppmWorkspace::module(auth()->user(), 'reports');
        $query = Report::with(['proposal'])->whereHas('proposal', fn ($q) => $this->scopeProposalVisibility($q));
        $progressBuckets = ['0-25' => (clone $query)->whereBetween('progress', [0, 25])->count(), '26-75' => (clone $query)->whereBetween('progress', [26, 75])->count(), '76-100' => (clone $query)->whereBetween('progress', [76, 100])->count()];
        $reports = $query->latest()->paginate(10);

        return view('reports.index', compact('reports', 'module', 'progressBuckets'));
    }
}
