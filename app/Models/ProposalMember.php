<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalMember extends Model
{
    use HasFactory;
    use UsesUuid;

    protected $primaryKey = 'member_id';

    protected $fillable = [
        'member_id',
        'proposal_id',
        'user_id',
        'role',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'proposal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
