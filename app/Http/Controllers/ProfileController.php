<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $recentActivities = \App\Models\ActivityLog::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('activity_type', 'like', '%Login%')
                      ->orWhere('activity_type', 'like', '%Logout%');
            })
            ->latest()
            ->take(4)
            ->get();

        // Get the accurate last profile update timestamp from activity logs
        $lastProfileUpdateLog = \App\Models\ActivityLog::where('user_id', $user->id)
            ->whereIn('activity_type', [
                'Profile Updated',
                'Password Changed',
                'Ganti Password',
                'User Diupdate'
            ])
            ->latest()
            ->first();

        // Fetch support email from LandingPageSetting (key: footer, attribute: email)
        $footerSetting = \App\Models\LandingPageSetting::where('key', 'footer')->first();
        $supportEmail = $footerSetting->content['email'] ?? 'hello@laundryan.com';

        return view('profile.edit', [
            'user' => $user,
            'recentActivities' => $recentActivities,
            'currentIp' => $request->ip(),
            'currentBrowser' => \App\Models\ActivityLog::parseBrowser($request->header('User-Agent')),
            'currentDevice' => \App\Models\ActivityLog::parseDevice($request->header('User-Agent')),
            'lastProfileUpdate' => $lastProfileUpdateLog ? $lastProfileUpdateLog->created_at : null,
            'supportEmail' => $supportEmail,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        $dataBefore = $user->only(['name', 'email', 'phone', 'address', 'photo']);
        
        $validated = $request->validated();
        
        $photoChanged = false;
        if ($request->boolean('remove_photo')) {
            if ($user->photo && \Storage::disk('public')->exists($user->photo)) {
                \Storage::disk('public')->delete($user->photo);
            }
            $user->photo = null;
            $photoChanged = true;
        } elseif ($request->hasFile('photo')) {
            if ($user->photo && \Storage::disk('public')->exists($user->photo)) {
                \Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('users', 'public');
            $photoChanged = true;
        }
        
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        $personalChanged = $user->isDirty(['name', 'email', 'phone', 'address']);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $passwordChanged = false;
        if (!empty($validated['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
            $passwordChanged = true;
        }

        $user->save();
        
        $dataAfter = $user->only(['name', 'email', 'phone', 'address', 'photo']);
        
        if ($passwordChanged) {
            \App\Models\ActivityLog::log(
                'Auth & Security',
                'Password Changed',
                'User "' . $user->name . '" changed password',
                'Auth',
                null,
                null,
                null,
                $user
            );
        }
        
        if ($photoChanged || $personalChanged) {
            \App\Models\ActivityLog::log(
                'Auth & Security',
                'Profile Updated',
                'User "' . $user->name . '" updated profile details',
                'Auth',
                null,
                $dataBefore,
                $dataAfter,
                $user
            );
        }

        $redirect = Redirect::route('profile.edit');
        
        $messages = [];
        if ($photoChanged) {
            $redirect = $redirect->with('photo_success', 'Successfully Updated');
            $messages[] = 'Profile photo';
        }
        if ($personalChanged) {
            $redirect = $redirect->with('personal_success', 'Successfully Updated');
            $messages[] = 'Personal information';
        }
        if ($passwordChanged) {
            $redirect = $redirect->with('password_success', 'Successfully Updated');
            $messages[] = 'Account security';
        }

        if (count($messages) > 0) {
            if (count($messages) === 1) {
                $toastMessage = $messages[0] . ' updated successfully.';
            } else {
                $toastMessage = implode(' and ', [implode(', ', array_slice($messages, 0, -1)), end($messages)]) . ' updated successfully.';
            }
            return $redirect->with('success', $toastMessage)
                            ->with('status', 'profile-updated');
        }

        return $redirect->with('info', 'No changes were made to your profile.')
                        ->with('status', 'profile-unchanged');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Account deleted successfully.');
    }
}
