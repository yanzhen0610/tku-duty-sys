<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserProfile extends Controller
{
    //
    public function self(Request $request)
    {
        return view('pages.user_self');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'min:4'
            ],
            'password_confirmation' => [
                'required',
            ],
        ]);

        $password = $request->input('password');
        $password_confirmation = $request->input('password_confirmation');

        if ($password != $password_confirmation)
            return back()->withErrors([
                'password_confirmation' => trans('user_profile.password_confirmation_not_matched'),
            ]);

        $user = Auth::user();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('status', trans('user_profile.reset_password_successfully'));
    }
}
