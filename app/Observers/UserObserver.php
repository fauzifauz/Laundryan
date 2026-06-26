<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ActivityLog;

class UserObserver
{
    public function created(User $user)
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : 'System';

        ActivityLog::log(
            'User Management',
            'User Added',
            'New user "' . $user->name . '" with role "' . ucfirst($user->role) . '" added',
            'User',
            $user->id,
            null,
            $user->toArray()
        );
    }

    public function updated(User $user)
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : 'System';

        // 1. Check status change
        if ($user->isDirty('status')) {
            $oldStatus = $user->getOriginal('status');
            $newStatus = $user->status;
            $statusTxt = $newStatus === 'active' ? 'active' : 'inactive';

            ActivityLog::log(
                'User Management',
                'User Status Changed',
                'Status for user "' . $user->name . '" changed to ' . $statusTxt,
                'User',
                $user->id,
                ['status' => $oldStatus],
                ['status' => $newStatus]
            );
        }

        // 2. Check role change
        if ($user->isDirty('role')) {
            $oldRole = $user->getOriginal('role');
            $newRole = $user->role;

            ActivityLog::log(
                'User Management',
                'User Role Changed',
                'Role for user "' . $user->name . '" changed to ' . ucfirst($newRole),
                'User',
                $user->id,
                ['role' => $oldRole],
                ['role' => $newRole]
            );
        }
    }

    public function deleted(User $user)
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : 'System';

        ActivityLog::log(
            'User Management',
            'User Deleted',
            'User "' . $user->name . '" has been deleted from the system',
            'User',
            $user->id,
            $user->toArray(),
            null
        );
    }
}
