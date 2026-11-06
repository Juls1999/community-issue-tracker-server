<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers(),],
            'barangay_id' => ['required', 'integer', 'exists:barangays,id'],
        ]);

        $residentRole = Role::where('name', 'resident')->firstOrFail();

        $user = DB::transaction(function () use ($validated, $residentRole) {
            $user = new User();

            $user->first_name = $validated['first_name'];
            $user->middle_name = $validated['middle_name'];
            $user->last_name = $validated['last_name'];
            $user->phone_number = $validated['phone_number'];
            $user->email = $validated['email'];
            $user->password = Hash::make($validated['password']);
            $user->barangay_id = $validated['barangay_id'];
            $user->role()->associate($residentRole);
            $user->save();

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
