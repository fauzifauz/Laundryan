<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public const LEAVE_PERMISSION_QUOTA_PER_YEAR = 12;

    protected $fillable = [
        'user_id', 
        'date', 
        'check_in', 
        'check_out', 
        'photo_path', 
        'status', 
        'latitude', 
        'longitude', 
        'location_name', 
        'approval_status', 
        'reject_reason', 
        'document_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function leavePermissionQuotaUsed(int $userId, ?int $year = null): int
    {
        $year = $year ?? now()->year;

        return static::where('user_id', $userId)
            ->whereIn('status', ['leave', 'permit'])
            ->whereYear('created_at', $year)
            ->count();
    }

    public static function canSubmitLeavePermission(int $userId, ?int $year = null): bool
    {
        return static::leavePermissionQuotaUsed($userId, $year) < self::LEAVE_PERMISSION_QUOTA_PER_YEAR;
    }
}
