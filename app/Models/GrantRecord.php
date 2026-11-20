<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrantRecord extends Model
{
    protected $fillable = ['proposal_id', 'actor_id', 'kind', 'from_stage', 'to_stage', 'note', 'data'];

    protected $casts = ['data' => 'array'];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'proposal_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'user_id');
    }
}
