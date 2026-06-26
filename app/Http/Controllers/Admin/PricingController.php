<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ItemType;
use App\Models\DeliveryFee;
use App\Models\Tax;
use Illuminate\Support\Facades\Storage;

class PricingController extends Controller
{
    public function indexServices(Request $request)
    {
        $services = Service::latest()->paginate(10);
        return view('admin.pricing.services', compact('services'));
    }

    public function indexItemTypes(Request $request)
    {
        $itemTypes = ItemType::latest()->paginate(10);
        return view('admin.pricing.item-types', compact('itemTypes'));
    }

    public function indexDeliveryFees(Request $request)
    {
        $deliveryFees = DeliveryFee::orderBy('min_distance')->paginate(10);
        return view('admin.pricing.delivery-fees', compact('deliveryFees'));
    }

    public function indexTaxes(Request $request)
    {
        $taxes = Tax::latest()->paginate(10);
        return view('admin.pricing.taxes', compact('taxes'));
    }

    // --- SERVICE ACTIONS ---
    public function storeService(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('services', 'public');
        }

        Service::create($data);
 
        return redirect()->route('admin.pricing.services')
            ->with('success', 'Laundry Service added successfully.')
            ->with('action_status', 'added');
    }
 
    public function updateService(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|max:2048',
        ]);
 
        $data['is_active'] = $request->has('is_active');
 
        if ($request->has('remove_photo') && $request->remove_photo) {
            if ($service->photo) {
                Storage::disk('public')->delete($service->photo);
            }
            $data['photo'] = null;
        } elseif ($request->hasFile('photo')) {
            if ($service->photo) {
                Storage::disk('public')->delete($service->photo);
            }
            $data['photo'] = $request->file('photo')->store('services', 'public');
        }
 
        $service->update($data);
 
        return redirect()->route('admin.pricing.services')
            ->with('success', 'Laundry Service updated successfully.')
            ->with('action_status', 'updated');
    }
 
    public function destroyService(Service $service)
    {
        if ($service->photo) {
            Storage::disk('public')->delete($service->photo);
        }
        $service->delete();
        return redirect()->route('admin.pricing.services')
            ->with('success', 'Laundry Service deleted successfully.')
            ->with('action_status', 'deleted');
    }
 
    public function toggleService(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);
        return redirect()->route('admin.pricing.services')
            ->with('success', 'Laundry Service updated successfully.')
            ->with('action_status', 'updated');
    }
 
    // --- ITEM TYPE ACTIONS ---
    public function storeItemType(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|max:2048',
        ]);
 
        $data['is_active'] = $request->has('is_active');
 
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('item_types', 'public');
        }
 
        ItemType::create($data);
 
        return redirect()->route('admin.pricing.item-types')
            ->with('success', 'Item Type added successfully.')
            ->with('action_status', 'added');
    }
 
    public function updateItemType(Request $request, ItemType $itemType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|max:2048',
        ]);
 
        $data['is_active'] = $request->has('is_active');
 
        if ($request->has('remove_photo') && $request->remove_photo) {
            if ($itemType->photo) {
                Storage::disk('public')->delete($itemType->photo);
            }
            $data['photo'] = null;
        } elseif ($request->hasFile('photo')) {
            if ($itemType->photo) {
                Storage::disk('public')->delete($itemType->photo);
            }
            $data['photo'] = $request->file('photo')->store('item_types', 'public');
        }
 
        $itemType->update($data);
 
        return redirect()->route('admin.pricing.item-types')
            ->with('success', 'Item Type updated successfully.')
            ->with('action_status', 'updated');
    }
 
    public function destroyItemType(ItemType $itemType)
    {
        if ($itemType->photo) {
            Storage::disk('public')->delete($itemType->photo);
        }
        $itemType->delete();
        return redirect()->route('admin.pricing.item-types')
            ->with('success', 'Item Type deleted successfully.')
            ->with('action_status', 'deleted');
    }
 
    public function toggleItemType(ItemType $itemType)
    {
        $itemType->update(['is_active' => !$itemType->is_active]);
        return redirect()->route('admin.pricing.item-types')
            ->with('success', 'Item Type updated successfully.')
            ->with('action_status', 'updated');
    }
 
    // --- DELIVERY FEE ACTIONS ---
    public function storeDeliveryFee(Request $request)
    {
        $data = $request->validate([
            'min_distance' => 'required|numeric|min:0',
            'max_distance' => 'required|numeric|gt:min_distance',
            'fee' => 'required|numeric|min:0',
            'min_fee' => 'required|numeric|min:0',
            'max_fee' => 'nullable|numeric|min:0',
        ]);
 
        $data['is_active'] = $request->has('is_active');
 
        DeliveryFee::create($data);
 
        return redirect()->route('admin.pricing.delivery-fees')
            ->with('success', 'Delivery Fee added successfully.')
            ->with('action_status', 'added');
    }
 
    public function updateDeliveryFee(Request $request, DeliveryFee $deliveryFee)
    {
        $data = $request->validate([
            'min_distance' => 'required|numeric|min:0',
            'max_distance' => 'required|numeric|gt:min_distance',
            'fee' => 'required|numeric|min:0',
            'min_fee' => 'required|numeric|min:0',
            'max_fee' => 'nullable|numeric|min:0',
        ]);
 
        $data['is_active'] = $request->has('is_active');
 
        $deliveryFee->update($data);
 
        return redirect()->route('admin.pricing.delivery-fees')
            ->with('success', 'Delivery Fee updated successfully.')
            ->with('action_status', 'updated');
    }
 
    public function destroyDeliveryFee(DeliveryFee $deliveryFee)
    {
        $deliveryFee->delete();
        return redirect()->route('admin.pricing.delivery-fees')
            ->with('success', 'Delivery Fee deleted successfully.')
            ->with('action_status', 'deleted');
    }
 
    public function toggleDeliveryFee(DeliveryFee $deliveryFee)
    {
        $deliveryFee->update(['is_active' => !$deliveryFee->is_active]);
        return redirect()->route('admin.pricing.delivery-fees')
            ->with('success', 'Delivery Fee updated successfully.')
            ->with('action_status', 'updated');
    }
 
    // --- TAX ACTIONS ---
    public function storeTax(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);
 
        $data['is_active'] = $request->has('is_active');
 
        Tax::create($data);
 
        return redirect()->route('admin.pricing.taxes')
            ->with('success', 'Tax Configuration added successfully.')
            ->with('action_status', 'added');
    }
 
    public function updateTax(Request $request, Tax $tax)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);
 
        $data['is_active'] = $request->has('is_active');
 
        $tax->update($data);
 
        return redirect()->route('admin.pricing.taxes')
            ->with('success', 'Tax Configuration updated successfully.')
            ->with('action_status', 'updated');
    }
 
    public function destroyTax(Tax $tax)
    {
        $tax->delete();
        return redirect()->route('admin.pricing.taxes')
            ->with('success', 'Tax Configuration deleted successfully.')
            ->with('action_status', 'deleted');
    }
 
    public function toggleTax(Tax $tax)
    {
        $tax->update(['is_active' => !$tax->is_active]);
        return redirect()->route('admin.pricing.taxes')
            ->with('success', 'Tax Configuration updated successfully.')
            ->with('action_status', 'updated');
    }
}
