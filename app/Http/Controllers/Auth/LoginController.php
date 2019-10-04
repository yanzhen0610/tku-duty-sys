<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\ReCAPTCHA;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    protected $maxAttempts = 5;
    protected $decayMinutes = 12;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->redirectTo = route('home');
    }

    public function username()
    {
        return 'username';
    }

    /**
     * Show the application's login form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request)
    {
        $verifyV2Checkbox = ReCAPTCHA::v2Available()
            && method_exists($this, 'hasTooManyLoginAttempts')
            && $this->hasTooManyLoginAttempts($request);
        $verifyV3 = ReCAPTCHA::v3Available();
        return view('auth.login', compact([
            'verifyV3',
            'verifyV2Checkbox',
        ]));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (ReCAPTCHA::v3Available())
        {
            $result = ReCAPTCHA::v3Verify($request->input('reCAPTCHA_v3_token'), $request->ip());
            abort_if(!$result, 500);
            if (!$result->success)
            {
                return back()->with('error_message', __('recaptcha.v3_verification_failed'));
            }
        }

        $this->verifyV2Checkbox($request);

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
    }

    protected function verifyV2Checkbox(Request $request)
    {
        if (ReCAPTCHA::v2Available() && $this->hasTooManyLoginAttempts($request))
        {
            $result = ReCAPTCHA::v2Verify($request->input('reCAPTCHA_v2_token'), $request->ip());
            abort_if(!$result, 500);
            if (!$result->success)
            {
                throw ValidationException::withMessages([
                    'reCAPTCHA_v2_token' => __('recaptcha.v2_checkbox_verification_failed'),
                ]);
            }
        }
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return $request->ip();
    }
}
