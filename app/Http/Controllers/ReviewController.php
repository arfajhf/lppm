<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesLppmProposalAccess;
use App\Models\GrantRecord;
use App\Models\Review;
use App\Support\LppmWorkspace;

class ReviewController extends Controller
{
    use AuthorizesLppmProposalAccess;

    public function index()
    {
        $module = LppmWorkspace::module(auth()->user(), 'reviews');
        $query = Review::with(['proposal', 'reviewer'])->whereHas('proposal', fn ($q) => $this->scopeProposalVisibility($q));
        $statusSummary = (clone $query)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $reviews = $query->latest()->paginate(10);

        $tasks = GrantRecord::with('proposal')->where('kind', 'assignment')
            ->whereHas('proposal', fn ($q) => $this->scopeProposalVisibility($q))
            ->whereHas('proposal', function ($q) {
                $q->where(function ($stages) {
                    foreach (['proposal' => 'review', 'monev' => 'monev', 'seminar' => 'seminar'] as $kind => $stage) {
                        $stages->orWhere(fn ($pair) => $pair->where('grant_stage', $stage)->where('grant_records.data->review_kind', $kind));
                    }
                });
            });
        $tasks->whereNotExists(function ($q) {
            $q->selectRaw('1')->from('grant_records as rounds')
                ->whereColumn('rounds.proposal_id', 'grant_records.proposal_id')
                ->whereColumn('rounds.id', '>', 'grant_records.id')->where('rounds.kind', 'resubmit')
                ->whereIn('rounds.to_stage', ['review', 'monev', 'seminar'])
                ->whereRaw("rounds.to_stage = CASE JSON_UNQUOTE(JSON_EXTRACT(grant_records.data, '$.review_kind')) WHEN 'proposal' THEN 'review' WHEN 'monev' THEN 'monev' WHEN 'seminar' THEN 'seminar' END");
        });
        if (! auth()->user()->hasLppmPermission('lppm.review.assign')) {
            $tasks->where('data->reviewer_id', auth()->id());
        }
        $grantTasks = $tasks->latest()->paginate(15, ['*'], 'tasks_page');

        return view('reviews.index', compact('reviews', 'module', 'statusSummary', 'grantTasks'));
    }
}
