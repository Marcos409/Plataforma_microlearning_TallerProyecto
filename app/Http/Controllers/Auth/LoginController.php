<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Where to redirect users after login.
     */
    protected function redirectTo()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if ($user) {
            if ($user->isStudent()) {
                return route('student.dashboard');
            } elseif ($user->isAdmin()) {
                return route('admin.dashboard');
            } elseif ($user->isTeacher()) {
                return route('teacher.dashboard');
            }
        }
        
        // Si no tiene ningún rol específico, redirigir a la página principal
        return '/';
    }
}