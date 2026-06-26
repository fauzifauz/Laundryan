<?php

namespace App\Observers;

use App\Models\Service;
use App\Models\ActivityLog;

class ServiceObserver
{
    public function updated(Service $service)
    {
        if ($service->isDirty('base_price')) {
            $oldPrice = 'Rp' . number_format($service->getOriginal('base_price'), 0, ',', '.');
            $newPrice = 'Rp' . number_format($service->base_price, 0, ',', '.');

            ActivityLog::log(
                'Settings & Configuration',
                'Service Price Changed',
                'Price for service "' . $service->name . '" changed from ' . $oldPrice . ' to ' . $newPrice,
                'Service',
                $service->id,
                ['base_price' => $service->getOriginal('base_price')],
                ['base_price' => $service->base_price]
            );
        }
    }
}
