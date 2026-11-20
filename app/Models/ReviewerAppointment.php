<?php

namespace App\Models;

use App\Services\GrantSop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReviewerAppointment extends Model
{
    protected $attributes = ['status' => 'registered', 'published' => false];

    protected $fillable = ['grant_call_id', 'user_id', 'origin', 'expertise', 'institution', 'status', 'published', 'portfolio_path', 'recommendation_path', 'decree_path', 'decree_number', 'valid_until', 'history'];

    protected $casts = ['published' => 'boolean', 'valid_until' => 'date', 'history' => 'array'];

    public function readyForAssignment(): bool
    {
        if ($this->status !== 'appointed' || ! $this->valid_until || $this->valid_until->lt(today())) {
            return false;
        }

        $sop = app(GrantSop::class);
        $decree = $sop->action('reviewer', $this, $this->origin === 'external' ? '7.4' : '6.4');
        if (! $decree || ! $this->portfolio_path || ! Storage::disk('local')->exists($this->portfolio_path)
            || ! $this->decree_path || ! Storage::disk('local')->exists($this->decree_path)) {
            return false;
        }

        return $this->origin === 'external' || ($this->published && $sop->action('reviewer', $this, '35.5') !== null);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
