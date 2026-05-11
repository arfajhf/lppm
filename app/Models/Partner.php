<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;
    use UsesUuid;

    protected $primaryKey = 'partner_id';

    protected $fillable = [
        'partner_id',
        'name',
        'institution_type',
        'email',
        'phone',
    ];

    public function proposals()
    {
        return $this->belongsToMany(Proposal::class, 'proposal_partner', 'partner_id', 'proposal_id')
            ->withTimestamps();
    }
}
