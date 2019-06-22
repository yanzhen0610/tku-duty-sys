<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdministrationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function changeUserPasswordPage(Request $request, User $user)
    {
        return view('admin.changeUserPassword', compact(['user']));
    }

    public function changeUserPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => [
                'required',
                'min:1',
            ],
        ]);

        $user->password = Hash::make($request->input('new_password'));
        $user->status = User::$STATUS_NORMAL;
        $user->save();

        return back()->with('status', 'success');
    }
}
