<?php

namespace App\Observers;

use App\Models\Tax;
use App\Models\ActivityLog;

class TaxObserver
{
    public function updated(Tax $tax)
    {
        if ($tax->isDirty('percentage')) {
            $oldPct = $tax->getOriginal('percentage') . '%';
            $newPct = $tax->percentage . '%';

            ActivityLog::log(
                'Settings & Configuration',
                'Tax Rate Changed',
                'Tax ' . $tax->name . ' changed from ' . $oldPct . ' to ' . $newPct,
                'Tax',
                $tax->id,
                ['percentage' => $tax->getOriginal('percentage')],
                ['percentage' => $tax->percentage]
            );
        }
    }
}
