<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesLppmProposalAccess;
use App\Models\Document;
use App\Support\LppmWorkspace;

class DocumentController extends Controller
{
    use AuthorizesLppmProposalAccess;

    public function index()
    {
        $module = LppmWorkspace::module(auth()->user(), 'documents');
        $query = Document::with(['proposal.user', 'uploader'])->whereHas('proposal', fn ($q) => $this->scopeProposalVisibility($q));
        $documentTypeSummary = (clone $query)->selectRaw('document_type, count(*) as total')->groupBy('document_type')->pluck('total', 'document_type');
        $documents = $query->latest()->paginate(10);

        return view('documents.index', compact('documents', 'module', 'documentTypeSummary'));
    }
}
