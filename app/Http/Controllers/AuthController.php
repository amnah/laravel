<?php

namespace App\Http\Controllers;

use App\Classes\BaseController;
use App\Mail\EmailConfirmationMail;
use App\Mail\PasswordResetMail;
use App\Models\PasswordReset;
use App\Models\User;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    use AuthenticatesUsers, RegistersUsers, SendsPasswordResetEmails, ResetsPasswords {
        AuthenticatesUsers::guard insteadof RegistersUsers;
        AuthenticatesUsers::guard insteadof ResetsPasswords;
        AuthenticatesUsers::redirectPath insteadof RegistersUsers;
        AuthenticatesUsers::redirectPath insteadof ResetsPasswords;
        AuthenticatesUsers::credentials insteadof ResetsPasswords;
        SendsPasswordResetEmails::broker insteadof ResetsPasswords;
    }

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest', ['except' => 'logout']);
        return parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {

            // check if user has confirmed his email address
            /** @var User $user */
            $user = $this->guard()->user();
            if (!$user->isConfirmed()) {
                $this->guard()->logout();
                return $this->sendUnconfirmedLoginResponse($request);
            }

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * @inheritdoc
     */
    protected function attemptLogin(Request $request)
    {
        // determine if we're logging in using email or username
        // @link https://laracasts.com/discuss/channels/general-discussion/log-in-with-username-or-email-in-laravel-5/replies/105088
        $email = $request->get('email');
        $field = filter_var($email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $password = $request->get('password');
        return $this->guard()->attempt([$field => $email, 'password' => $password], $request->has('remember'));
    }

    /**
     * Send unconfirmed email response
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendUnconfirmedLoginResponse(Request $request)
    {
        $errors = [$this->username() => trans('auth.unconfirmed')];

        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }

        return back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors);
    }

    /**
     * @inheritdoc
     */
    public function register(Request $request)
    {
        $data = $request->all();
        Validator::make($data, [
            'email' => 'required|string|email|max:255|unique:user',
            'username' => 'required|string|max:255|unique:user',
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'confirmation' => $this->emailConfirmation ? Str::random(60) : null,
        ]);
        event(new Registered($user));

        return $this->registered($request, $user);
    }

    /**
     * Password rules for registration and resetting
     * @return string
     */
    protected function passwordRules()
    {
        return 'required|string|min:3|confirmed';
    }

    /**
     * @inheritdoc
     */
    protected function registered(Request $request, $user)
    {
        if ($this->emailConfirmation) {
            Mail::to($user->email)->send(new EmailConfirmationMail($user));
            return view('auth.registered', compact('user'));
        }

        $this->guard()->login($user);
        return redirect($this->redirectPath());
    }

    /**
     * Confirm user registration
     * @param Request $request
     * @param string $email
     * @param string $confirmation
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function confirm(Request $request, $email, $confirmation)
    {
        // find and confirm user
        /** @var User $user */
        $user = User::where([
            'email' => $email,
            'confirmation' => $confirmation,
        ])->first();

        $status = null;
        if ($user) {
            $user->confirmEmail();
            $status = trans('auth.confirmed', ['email' => $user->email]);

            // log in and set session message
            // comment these lines out if you don't want to log in automatically
            $this->guard()->login($user);
            session()->put('status', $status);
            return redirect($this->redirectPath());
        }

        return view('auth.confirm', compact('status'));
    }

    /**
     * @inheritdoc
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot');
    }

    /**
     * @inheritdoc
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        // send response immediately if we can't find the user
        $email = $request->get('email');
        $user = User::where("email", $email)->first();
        if (!$user) {
            // send failed response
            return $this->sendResetLinkFailedResponse($request, Password::INVALID_USER);

            // send fake success response for security (make sure you comment out then line above)
            //return $this->sendResetLinkResponse(Password::RESET_LINK_SENT, $email);
        }

        // set token for user and send email
        $passwordReset = PasswordReset::setTokenForUser($user->id);
        Mail::to($user->email)->send(new PasswordResetMail($user, $passwordReset));

        // send success response
        return $this->sendResetLinkResponse(Password::RESET_LINK_SENT, $email);
    }

    /**
     * @inheritdoc
     */
    protected function sendResetLinkResponse($response, $email = null)
    {
        return back()
            ->withInput()
            ->with('status', trans($response))
            ->with('email', $email);
    }

    /**
     * @inheritdoc
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return back()
            ->withInput()
            ->withErrors(['email' => trans($response)]);
    }

    /**
     * @inheritdoc
     */
    public function showResetForm(Request $request, $token)
    {
        $passwordReset = PasswordReset::getByToken($token);
        $email = '';
        if ($passwordReset) {
            $email = User::where('id', $passwordReset->user_id)->value('email');
        }
        return view('auth.reset')->with([
            'passwordReset' => $passwordReset,
            'email' => $email,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function reset(Request $request, $token)
    {
        $rules = ['password' => $this->passwordRules()];
        $this->validate($request, $rules, $this->validationErrorMessages());

        // check for passwordReset and user
        /** @var User $user */
        $passwordReset = PasswordReset::getByToken($token);
        if (!$passwordReset) {
            return $this->sendResetFailedResponse($request, Password::INVALID_USER);
        }
        $user = $passwordReset->user;
        if (!$user) {
            return $this->sendResetFailedResponse($request, Password::INVALID_USER);
        }

        // update password and log user in
        $password = Hash::make($request->get('password'));
        $user->setAttribute('password', $password)->confirmEmail();
        $this->guard()->login($user);

        // consume the password reset model and send success
        $passwordReset->consume();
        return $this->sendResetResponse($request, Password::PASSWORD_RESET);
    }

    /**
     * @inheritdoc
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return back()
            ->withInput()
            ->withErrors(['email' => trans($response)]);
    }
}
