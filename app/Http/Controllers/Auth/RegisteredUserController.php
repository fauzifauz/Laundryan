<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminNewUserNotificationMail;
use App\Mail\StandardWelcomeMail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:15', 'regex:/^[0-9]{8,15}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone.regex' => 'The phone number must contain only digits (8-15 digits).',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => '+62' . $request->phone,
            'role' => 'pelanggan',
            'status' => 'active',
            'password' => Hash::make($request->password),
        ]);

        // Send Welcome Email to User
        Mail::to($user->email)->send(new StandardWelcomeMail($user));

        // Notify Admin
        $adminEmail = env('ADMIN_EMAIL', 'ithelpsdesk1@gmail.com');
        Mail::to($adminEmail)->send(new AdminNewUserNotificationMail($user, 'Form'));

        event(new Registered($user));

        // Redirect to login with status message
        return redirect()->route('login')->with('status', 'Registration successful! You can now log in to your account.');

    }
}
