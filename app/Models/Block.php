<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    protected $table = 'blocked_users';

    protected $primaryKey = 'Block_ID';

    public $timestamps = false;

    protected $fillable = ['Blocker_ID', 'Blocked_User_ID', 'Created_At'];

    protected $casts = [
        'Created_At' => 'datetime',
    ];

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Blocker_ID', 'User_ID');
    }

    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Blocked_User_ID', 'User_ID');
    }

    public static function isBlocked(int $blockerId, int $blockedId): bool
    {
        return self::where('Blocker_ID', $blockerId)
            ->where('Blocked_User_ID', $blockedId)
            ->exists();
    }
}
