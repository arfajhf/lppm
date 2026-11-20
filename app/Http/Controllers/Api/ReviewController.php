<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $r)
    {
        abort_unless($r->user()->hasLppmPermission('lppm.review.score'), 403);
        $v = $r->validate(['review_id' => 'required|exists:reviews,review_id', 'score' => 'required|numeric|between:0,100', 'comment' => 'required|string|max:10000', 'reviewer_id' => 'prohibited', 'status' => 'prohibited', 'proposal_id' => 'prohibited']);
        $review = Review::findOrFail($v['review_id']);
        abort_unless($review->reviewer_id === $r->user()->user_id && ! $review->proposal->grant_stage && $review->proposal->status === 'review', 403);
        $review->update(['score' => $v['score'], 'comment' => $v['comment'], 'status' => 'completed']);

        return response()->json($review);
    }
}
