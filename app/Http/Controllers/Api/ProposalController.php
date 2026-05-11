<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;

/**
 * Menyediakan endpoint API dasar untuk daftar dan pembuatan proposal.
 */
class ProposalController extends Controller
{
    /**
     * Mengembalikan daftar proposal terbaru beserta relasi utama untuk konsumsi API.
     */
    public function index()
    {
        return response()->json(
            Proposal::with(['user', 'reviews'])->latest()->paginate(10)
        );
    }

    /**
     * Memvalidasi lalu menyimpan proposal baru melalui antarmuka API.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'scheme' => ['required', 'string', 'max:100'],
            'user_id' => ['required', 'exists:users,user_id'],
            'status' => ['required', 'string', 'max:30'],
            'year' => ['required', 'digits:4'],
            'summary' => ['nullable', 'string'],
        ]);

        $proposal = Proposal::create($validated);

        return response()->json($proposal, 201);
    }
}
