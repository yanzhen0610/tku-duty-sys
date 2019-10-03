<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\ReCAPTCHA;
use App\Events\ResetPasswordRequested;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Display the form to request to reset a user's password.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRequestResetForm()
    {
        return view('auth.passwords.requestReset');
    }

    public function requestReset(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'reCAPTCHA_v3_token' => [],
            'reCAPTCHA_v2_token' => [],
        ]);

        if (ReCAPTCHA::v3Available())
        {
            $result = ReCAPTCHA::v3Verify($request->input('reCAPTCHA_v3_token'), $request->ip());
            abort_if(!$result, 500);
            if (!$result->success)
            {
                return back()->with('error_message', __('recaptcha.v3_verification_failed'));
            }
        }

        if (ReCAPTCHA::v2Available())
        {
            $result = ReCAPTCHA::v2Verify($request->input('reCAPTCHA_v2_token'), $request->ip());
            abort_if(!$result, 500);
            if (!$result->success)
            {
                return back()->with('error_message', __('recaptcha.v2_checkbox_verification_failed'));
            }
        }

        if ($user = User::where('username', $request->input('username'))->first())
        {
            $user->status = User::$STATUS_RESET_PASSWORD_REQUESTED;
            $user->save();
        }

        event(new ResetPasswordRequested($request->input('username')));

        return back()->with('status', trans('passwords.reset_request_send_to_admin'));
    }
}
