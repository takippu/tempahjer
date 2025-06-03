<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'subdomain' => 'required|string|max:255|alpha_dash|unique:domains,domain',
        ]);

        // Create the tenant with tenant_userName format
        $tenant = Tenant::create([
            'id' => 'tenant_' . $request->subdomain,
        ]);

        // Create the domain for the tenant
        $tenant->domains()->create([
            'domain' => $request->subdomain . '.tempahjer.test',
        ]);

        // Run tenant migrations
        $tenant->run(function () use ($request) {
            // Create user in tenant database
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        });

        // Create user in central database as well for authentication
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirect to central dashboard after successful registration
        return redirect()->route('dashboard');
    }
}
