<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityNotification extends Model
{
    use HasFactory;
    use UsesUuid;

    protected $table = 'notifications';

    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'notification_id',
        'user_id',
        'message',
        'is_read',
        'channel',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
