<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    /**
     * Mark the onboarding as completed for the authenticated pelanggan.
     * Special case: pelanggan@laundryan.com is never marked as completed
     * so the developer can always test the onboarding flow.
     */
    public function complete(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Skip persisting for the developer testing account
        if ($user->email !== 'pelanggan@laundryan.com') {
            $user->update([
                'onboarding_completed_at' => now(),
            ]);
        }

        // Clear the session flag
        $request->session()->forget('show_onboarding');

        return response()->json(['status' => 'ok']);
    }
}
