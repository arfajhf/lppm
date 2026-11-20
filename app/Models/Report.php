<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    use UsesUuid;

    protected $primaryKey = 'report_id';

    protected $fillable = [
        'report_id', 'report_type', 'document_id', 'report_data', 'submitted_by', 'accepted_by', 'accepted_at', 'receipt_reference',
        'proposal_id',
        'progress',
        'description',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime', 'accepted_at' => 'datetime', 'report_data' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'document_id');
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'proposal_id');
    }
}
