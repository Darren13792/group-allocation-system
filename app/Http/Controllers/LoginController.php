<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function index() 
    {
        // Check if the user is logged in
        if (Auth()->check()) {
            return redirect(route('home'));
        }
        // If logged out, display login form
        return Inertia::render('Login/Login');
    }

    // Handle the login form submission
    public function login() {
        validator(request()->all(), [
            'email' => 'required',
            'password' => 'required'
        ])->validate();

        if(auth()->attempt(request()->only('email', 'password'))){
            
            return redirect(route('home'));
        } else {
            return redirect()->back()->withErrors([
                'password' => 'Email or password is incorrect'
            ])->withInput();
        }
    }

    // Request forgot password reset
    public function request_password() {
        validator(request()->all(), [
            'email' => 'required|email|exists:users',
        ],
        [
            'exists' => 'The email provided is not associated with any registered account.'
        ])->validate();

        $status = Password::sendResetLink(
            request()->only('email')
        );
        return $status === Password::RESET_LINK_SENT
                ? redirect()->route('forgot_password_success')
                : redirect()->back()->withErrors(['email' => __($status)]);
    }

    public function reset_password() {
        validator(request()->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ])->validate();

        $status = Password::reset(
            request()->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ]);
     
                $user->save();
     
                event(new PasswordReset($user));
            }
        );
     
        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('success', 'Password reset successful.')
                    : redirect()->back()->withErrors(['email' => [__($status)]]);
    }

    public function process_first_time_login() {
        validator(request()->all(), [
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ])->validate();

        $user = User::find(request()['user_id']);
        $user->update([
            'password' => bcrypt(request()['password']),
            'activation_status' => 1,
        ]);
    }
}
