<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

/**
 * Menyediakan endpoint API untuk pencatatan hasil review proposal.
 */
class ReviewController extends Controller
{
    /**
     * Memvalidasi payload dan menyimpan entri review baru melalui API.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'proposal_id' => ['required', 'exists:proposals,proposal_id'],
            'reviewer_id' => ['required', 'exists:users,user_id'],
            'score' => ['nullable', 'numeric', 'between:0,100'],
            'comment' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:30'],
        ]);

        $review = Review::create($validated);

        return response()->json($review, 201);
    }
}
