<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopAction extends Model
{
    protected $fillable = ['scope', 'subject_id', 'step_key', 'actor_id', 'fields', 'files', 'cycle', 'due_at'];

    protected $casts = ['fields' => 'array', 'files' => 'array', 'due_at' => 'datetime', 'cycle' => 'integer'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'user_id');
    }
}
