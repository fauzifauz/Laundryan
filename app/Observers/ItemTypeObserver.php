<?php

namespace App\Observers;

use App\Models\ItemType;
use App\Models\ActivityLog;

class ItemTypeObserver
{
    public function updated(ItemType $itemType)
    {
        if ($itemType->isDirty('base_price')) {
            $oldPrice = 'Rp' . number_format($itemType->getOriginal('base_price'), 0, ',', '.');
            $newPrice = 'Rp' . number_format($itemType->base_price, 0, ',', '.');

            ActivityLog::log(
                'Settings & Configuration',
                'Item Type Price Changed',
                'Price for item type "' . $itemType->name . '" changed from ' . $oldPrice . ' to ' . $newPrice,
                'ItemType',
                $itemType->id,
                ['base_price' => $itemType->getOriginal('base_price')],
                ['base_price' => $itemType->base_price]
            );
        }
    }
}
