<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemTypeController extends Controller
{
    public function index()
    {
        $itemTypes = \App\Models\ItemType::latest()->get();
        return view('admin.item_types.index', compact('itemTypes'));
    }

    public function create()
    {
        return view('admin.item_types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
        ]);

        $data['is_active'] = $request->has('is_active');

        \App\Models\ItemType::create($data);
        return redirect()->route('admin.item-types.index')->with('success', 'Item Type created successfully.');
    }

    public function edit(\App\Models\ItemType $itemType)
    {
        return view('admin.item_types.edit', compact('itemType'));
    }

    public function update(Request $request, \App\Models\ItemType $itemType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
        ]);

        $data['is_active'] = $request->has('is_active');

        $itemType->update($data);
        return redirect()->route('admin.item-types.index')->with('success', 'Item Type updated successfully.');
    }

    public function destroy(\App\Models\ItemType $itemType)
    {
        $itemType->delete();
        return redirect()->route('admin.item-types.index')->with('success', 'Item Type deleted successfully.');
    }
}
