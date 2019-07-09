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
            'current_password' => [
                'required',
            ],
            'new_password' => [
                'required',
                'min:4',
            ],
            'password_confirmation' => [
                'required',
                'same:new_password',
            ],
        ]);

        $current_password = $request->input('current_password');
        if (!Hash::check($current_password, Auth::user()->password))
            return back()->withErrors([
                'current_password' => trans('user_profile.current_password_not_matched'),
            ]);

        $user = Auth::user();
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return back()->with('status', trans('user_profile.reset_password_successfully'));
    }
}
