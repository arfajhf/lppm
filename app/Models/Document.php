<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    use UsesUuid;

    protected $primaryKey = 'document_id';

    protected $fillable = [
        'document_id',
        'proposal_id',
        'file_name',
        'file_path',
        'document_type',
        'uploaded_by',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'proposal_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'user_id');
    }
}
