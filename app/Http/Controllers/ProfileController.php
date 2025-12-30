<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * View profile
     */
    public function show()
    {
        return view('profile.show');
    }

    /**
     * Update profile
     */
    public function update(UpdateProfileRequest $request)
    {
        Auth::user()->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Profil mis à jour avec succès !');
    }

    /**
     * Update password
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Mot de passe changé avec succès !');
    }
}
