<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\ActivityLog;

class AttendanceObserver
{
    public function created(Attendance $attendance)
    {
        $user = $attendance->user;
        $userName = $user ? $user->name : 'User';
        $roleName = $user ? ucfirst($user->role) : 'User';

        if ($attendance->check_in) {
            ActivityLog::log(
                'Payroll & Attendance',
                'Check In',
                $roleName . ' "' . $userName . '" performed Check In',
                'Attendance',
                $attendance->id,
                null,
                $attendance->toArray(),
                $user
            );
        } else {
            // This is a permit or leave request
            $typeLabel = $attendance->status === 'permit' ? 'Permit' : 'Leave';
            ActivityLog::log(
                'Payroll & Attendance',
                'Permit/Leave Requested',
                $roleName . ' "' . $userName . '" requested ' . $typeLabel . ' for ' . $attendance->date,
                'Attendance',
                $attendance->id,
                null,
                $attendance->toArray(),
                $user
            );
        }
    }

    public function updated(Attendance $attendance)
    {
        $user = $attendance->user;
        $userName = $user ? $user->name : 'User';
        $roleName = $user ? ucfirst($user->role) : 'User';

        // 1. Check Out
        if ($attendance->isDirty('check_out') && $attendance->check_out && !$attendance->getOriginal('check_out')) {
            ActivityLog::log(
                'Payroll & Attendance',
                'Check Out',
                $roleName . ' "' . $userName . '" performed Check Out',
                'Attendance',
                $attendance->id,
                null,
                $attendance->toArray(),
                $user
            );
        }

        // 2. Approval Status Change
        if ($attendance->isDirty('approval_status')) {
            $oldStatus = $attendance->getOriginal('approval_status');
            $newStatus = $attendance->approval_status;
            
            $actor = auth()->user();
            $actorName = $actor ? $actor->name : 'System';
            $actorRole = $actor ? ucfirst($actor->role) : 'System';

            $action = $newStatus === 'approved' ? 'Approved' : 'Rejected';
            $typeLabel = $attendance->status === 'permit' ? 'Permit' : 'Leave';

            ActivityLog::log(
                'Payroll & Attendance',
                'Permit/Leave ' . $action,
                $typeLabel . ' request for ' . $roleName . ' "' . $userName . '" on date ' . $attendance->date . ' has been ' . $newStatus . ' by ' . $actorRole . ' ' . $actorName,
                'Attendance',
                $attendance->id,
                ['approval_status' => $oldStatus],
                ['approval_status' => $newStatus]
            );
        }
    }
}
