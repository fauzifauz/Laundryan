<?php

namespace App\Observers;

use App\Models\LandingPageSetting;
use App\Models\ActivityLog;

class LandingPageSettingObserver
{
    public function updated(LandingPageSetting $setting)
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : 'Admin';

        ActivityLog::log(
            'Settings & Configuration',
            'Landing Page Updated',
            'Landing page content (' . ucfirst($setting->key) . ' section) updated by ' . $actorName,
            'LandingPageSetting',
            $setting->id,
            $setting->getOriginal('content'),
            $setting->content
        );
    }
}
