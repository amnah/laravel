<?php

namespace App\Http\Controllers;

use App\Classes\BaseController;
use App\Mail\EmailConfirmationMail;
use App\Mail\PasswordResetMail;
use App\Models\PasswordReset;
use App\Models\User;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class AuthController extends BaseController
{
    use ThrottlesLogins;

    /**
     * Where to redirect users after login/logout
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Email confirmation after registration.
     * @var bool
     */
    protected $emailConfirmation = true;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'postLogout']);
        return parent::__construct();
    }

    /**
     * Logout page
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postLogout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();

        return redirect($this->redirectTo);
    }

    /**
     * Login page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getLogin(Request $request)
    {
        return view('auth/login');
    }

    /**
     * Login page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function postLogin(Request $request)
    {
        // validate inputs
        $v = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($v->fails()) {
            return $this->sendFailedLoginResponse($request, $v->errors());
        }

        // check for login attempts
        $errors = new MessageBag();
        $userField = $this->username();
        if ($this->hasTooManyLoginAttempts($request)) {
            $seconds = $this->limiter()->availableIn($this->throttleKey($request));
            $message = __('auth.throttle', ['seconds' => $seconds]);
            return $this->sendFailedLoginResponse($request, $errors->add($userField, $message));
        }

        // compute credentials and attempt to log user in (user can log in via email or username)
        // @link https://laracasts.com/discuss/channels/general-discussion/log-in-with-username-or-email-in-laravel-5/replies/105088
        $dbField = filter_var($request->get($userField), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $dbField => $request->get($userField),
            'password' => $request->get('password'),
        ];
        if (!auth()->attempt($credentials, $request->has('remember'))) {
            return $this->sendFailedLoginResponse($request, $errors->add($userField, __('auth.failed')));
        }

        // check if user has been confirmed
        /** @var User $user */
        $user = auth()->user();
        if (!$user->isConfirmed()) {
            auth()->logout();
            return $this->sendFailedLoginResponse($request, $errors->add($userField, __('auth.unconfirmed')));
        }

        // clean up and redirect user to intended path
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);
        return redirect()->intended($this->redirectTo);
    }

    /**
     * Username field (needed for ThrottlesLogins)
     * @return string
     */
    protected function username()
    {
        return 'email';
    }

    /**
     * Send failed login response
     * @param Request $request
     * @param MessageBag $errors
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function sendFailedLoginResponse(Request $request, $errors)
    {
        $this->incrementLoginAttempts($request);
        return view('auth/login', ['errors' => $errors]);
    }

    /**
     * Register page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getRegister(Request $request)
    {
        return view('auth/register');
    }

    /**
     * Register page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function postRegister(Request $request)
    {
        // validate inputs
        $data = $request->all();
        $v = Validator::make($data, [
            'email' => 'required|string|email|max:255|unique:user',
            'username' => 'required|string|max:255|unique:user',
            'password' => User::passwordRules(),
        ]);
        if ($v->fails()) {
            return view('auth/register', ['errors' => $v->errors()]);
        }

        // create user
        $user = new User([
            'email' => $data['email'],
            'username' => $data['username'],
            'confirmation' => $this->emailConfirmation ? str_random(60) : null,
        ]);
        $user->setPassword($data['password'])->save();

        // send email confirm or simply log in and redirect
        if ($this->emailConfirmation) {
            Mail::to($user->email)->send(new EmailConfirmationMail($user));
            return view('auth/registered', compact('user'));
        }
        auth()->login($user);
        return redirect($this->redirectTo);
    }

    /**
     * Confirm user page
     * @param Request $request
     * @param string $email
     * @param string $confirmation
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getConfirm(Request $request, $email, $confirmation)
    {
        // find and confirm user
        /** @var User $user */
        $success = null;
        $user = User::where([
            'email' => $email,
            'confirmation' => $confirmation,
        ])->first();
        if ($user) {
            $user->confirmEmail();
            auth()->login($user);
            $success = __('auth.confirmed', ['email' => $user->email]);
        }

        return view('auth/confirm', compact('success'));
    }

    /**
     * Forgot page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getForgot(Request $request)
    {
        return view('auth/forgot');
    }

    /**
     * Forgot page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function postForgot(Request $request)
    {
        // validate inputs
        $v = Validator::make($request->all(), ['email' => 'required|email']);
        if ($v->fails()) {
            return view('auth/forgot', ['errors' => $v->errors()]);
        }

        // check for valid user
        $email = $request->get('email');
        $user = User::where("email", $email)->first();
        if (!$user) {
            // send fake success response for security
            //return view('auth/forgot', ['success' => __('passwords.sent')]);

            return view('auth/forgot', ['errors' => $v->errors()->add('email', __('passwords.user'))]);
        }

        // set token for user and send email
        $passwordReset = PasswordReset::setTokenForUser($user->id);
        Mail::to($user->email)->send(new PasswordResetMail($user, $passwordReset));

        return view('auth/forgot', ['success' => __('passwords.sent')]);
    }

    /**
     * Reset page
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getReset(Request $request, $token)
    {
        $passwordReset = PasswordReset::getByToken($token);
        return view('auth/reset', compact('passwordReset'));
    }

    /**
     * Reset page
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postReset(Request $request, $token)
    {
        // check for passwordReset and user
        /** @var User $user */
        $passwordReset = PasswordReset::getByToken($token);
        $user = $passwordReset->user ?? null;
        if (!$user) {
            return view('auth/reset');
        }

        // validate inputs
        $v = Validator::make($request->all(), ['password' => User::passwordRules()]);
        if ($v->fails()) {
            return view('auth/reset', ['passwordReset' => $passwordReset, 'errors' => $v->errors()]);
        }

        // update password and consume the $passwordReset
        $user->setPassword($request->get('password'))->confirmEmail();
        $passwordReset->consume();
        return view('auth/reset', ['success' => __('passwords.reset')]);
    }
}
