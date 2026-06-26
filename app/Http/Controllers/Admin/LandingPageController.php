<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LandingPageSetting;

class LandingPageController extends Controller
{
    public function index()
    {
        $settings = LandingPageSetting::all()->pluck('content', 'key');
        return view('admin.landing-page.index', compact('settings'));
    }

    public function update(Request $request, $key)
    {
        $setting = LandingPageSetting::firstOrCreate(
            ['key' => $key],
            ['content' => []]
        );
        $updatedKey = $request->input('section_subtype', $key);
        $data = $request->except(['_token', 'section_subtype']);

        // Reset dynamic lists if they are empty/not present in the request
        if ($key === 'services' && !isset($data['items'])) {
            $data['items'] = [];
        }
        if ($key === 'process' && !isset($data['steps'])) {
            $data['steps'] = [];
        }
        if ($key === 'benefits' && !isset($data['items'])) {
            $data['items'] = [];
        }
        if ($key === 'pricing' && !isset($data['plans'])) {
            $data['plans'] = [];
        }
        if ($key === 'faqs' && !isset($data['items'])) {
            $data['items'] = [];
        }
        if ($key === 'reviews' && !isset($data['items'])) {
            $data['items'] = [];
        }

        // Handle logo file upload
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $path = $file->store('logos', 'public');
            $data['logo_url'] = '/storage/' . $path;
            unset($data['logo_file']);
        }

        // Handle hero image file upload
        if ($request->hasFile('hero_image_file')) {
            $file = $request->file('hero_image_file');
            $path = $file->store('hero', 'public');
            $data['image_url'] = '/storage/' . $path;
            unset($data['hero_image_file']);
        }

        // Handle reviews avatar file uploads
        if ($key === 'reviews' && isset($data['items'])) {
            $items = $data['items'];
            if ($request->hasFile('items')) {
                $files = $request->file('items');
                foreach ($files as $i => $fileData) {
                    if (isset($fileData['avatar_file'])) {
                        $file = $fileData['avatar_file'];
                        $path = $file->store('avatars', 'public');
                        $items[$i]['avatar'] = '/storage/' . $path;
                    }
                }
            }
            // Clean up avatar_file from the items array
            foreach ($items as $i => $item) {
                unset($items[$i]['avatar_file']);
            }
            $data['items'] = $items;
        }

        // Merge with existing content to prevent losing data (like logo_url when updating name)
        $newContent = array_merge($setting->content ?? [], $data);

        // Handle pricing features conversion
        if ($key === 'pricing' && isset($newContent['plans'])) {
            foreach ($newContent['plans'] as $i => $plan) {
                if (isset($plan['features_raw'])) {
                    $newContent['plans'][$i]['features'] = array_map('trim', explode(',', $plan['features_raw']));
                    unset($newContent['plans'][$i]['features_raw']);
                }
                // Handle checkbox
                $newContent['plans'][$i]['popular'] = isset($plan['popular']);
            }
        }

        $setting->update([
            'content' => $newContent
        ]);

        $names = [
            'hero' => 'Campaign Settings',
            'services' => 'Services Catalog',
            'process' => 'Process Steps',
            'benefits' => 'Core Benefits',
            'pricing' => 'Pricing Plans',
            'faqs' => 'FAQ Accordion',
            'reviews' => 'Testimonials',
            'location' => 'Location Map',
            'footer_cta' => 'Footer CTA',
            'site' => 'Brand Identity',
            'contact' => 'Contact Details',
            'socials' => 'Social Media'
        ];

        $displayName = $names[$updatedKey] ?? ucfirst($key);
        $message = "$displayName updated successfully.";

        return back()
            ->with('success', $message)
            ->with('updated_key', $updatedKey);
    }
}
