<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrantCall extends Model
{
    protected $attributes = ['purpose' => 'grant', 'published' => false];

    protected $fillable = ['purpose', 'announcement_path', 'schedule_exception', 'title', 'guidelines', 'opens_at', 'closes_at', 'published'];

    protected $casts = ['opens_at' => 'date', 'closes_at' => 'date', 'published' => 'boolean'];

    public function isOpen(): bool
    {
        return $this->published && today()->between($this->opens_at, $this->closes_at);
    }
}
