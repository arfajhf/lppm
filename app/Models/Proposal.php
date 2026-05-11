<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;
    use UsesUuid;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_KAPRODI = 'pending_kaprodi';
    public const STATUS_PENDING_DEKAN = 'pending_dekan';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVIEW = 'review';
    public const STATUS_REVISION = 'revision';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_COMPLETED = 'completed';

    protected $primaryKey = 'proposal_id';

    protected $fillable = [
        'proposal_id',
        'title',
        'type',
        'scheme',
        'user_id',
        'status',
        'year',
        'summary',
        'submitted_at',
        'kaprodi_approved_by',
        'kaprodi_approved_at',
        'dekan_approved_by',
        'dekan_approved_at',
        'lppm_received_by',
        'lppm_received_at',
        'last_decision_stage',
        'last_decision_type',
        'last_decision_note',
        'last_decision_by',
        'last_decision_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'kaprodi_approved_at' => 'datetime',
        'dekan_approved_at' => 'datetime',
        'lppm_received_at' => 'datetime',
        'last_decision_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function members()
    {
        return $this->hasMany(ProposalMember::class, 'proposal_id', 'proposal_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'proposal_id', 'proposal_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'proposal_id', 'proposal_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'proposal_id', 'proposal_id');
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class, 'proposal_partner', 'proposal_id', 'partner_id')
            ->withTimestamps();
    }

    public function kaprodiApprover()
    {
        return $this->belongsTo(User::class, 'kaprodi_approved_by', 'user_id');
    }

    public function dekanApprover()
    {
        return $this->belongsTo(User::class, 'dekan_approved_by', 'user_id');
    }

    public function lppmReceiver()
    {
        return $this->belongsTo(User::class, 'lppm_received_by', 'user_id');
    }

    public function lastDecisionMaker()
    {
        return $this->belongsTo(User::class, 'last_decision_by', 'user_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_KAPRODI,
            self::STATUS_PENDING_DEKAN,
            self::STATUS_SUBMITTED,
            self::STATUS_REVIEW,
            self::STATUS_REVISION,
            self::STATUS_REJECTED,
            self::STATUS_APPROVED,
            self::STATUS_COMPLETED,
        ];
    }

    public static function labelForStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_KAPRODI => 'Menunggu Kaprodi',
            self::STATUS_PENDING_DEKAN => 'Menunggu Dekan',
            self::STATUS_SUBMITTED => 'Menunggu LPPM',
            self::STATUS_REVIEW => 'Dalam Review',
            self::STATUS_REVISION => 'Revisi',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_COMPLETED => 'Selesai',
            default => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }
}
