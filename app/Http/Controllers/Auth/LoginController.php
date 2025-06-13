<?php

// namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller;
// use Illuminate\Foundation\Auth\AuthenticatesUsers;

// class LoginController extends Controller
// {
//     /*
//     |--------------------------------------------------------------------------
//     | Login Controller
//     |--------------------------------------------------------------------------
//     |
//     | This controller handles authenticating users for the application and
//     | redirecting them to your home screen. The controller uses a trait
//     | to conveniently provide its functionality to your applications.
//     |
//     */

//     use AuthenticatesUsers;

//     /**
//      * Where to redirect users after login.
//      *
//      * @var string
//      */
//     protected $redirectTo = '/home';

//     /**
//      * Create a new controller instance.
//      *
//      * @return void
//      */
//     public function __construct()
//     {
//         $this->middleware('guest')->except('logout');
//         $this->middleware('auth')->only('logout');
//     }
// }

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->asForm()->post('https://gisapis.manpits.xyz/api/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // Simpan token dan data user
            if (isset($data['meta']['token'])) {
                session(['api_token' => $data['meta']['token']]);
            
                // Kalau ada user info, simpan juga, kalau tidak abaikan
                session(['user' => $data['user'] ?? null]);
            
                return redirect($this->redirectTo)->with('success', 'Login successful!');
            }
            
        }

        if (!$response->successful()) {
            $error = $response->json('message') ?? 'Login failed. Silakan cek kembali email atau password Anda.';
            return redirect()->back()->withErrors(['error' => $error])->withInput();
        }
        

        Log::error('Login API failed: ' . $response->body());
        return redirect()->back()->withErrors(['error' => 'Login failed.'])->withInput();
    }

    public function logout(Request $request)
    {
        $apiToken = session('api_token');

        if ($apiToken) {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Accept' => 'application/json',
            ])->post('https://gisapis.manpits.xyz/api/logout');
        }

        Session::flush();
        return redirect('/')->with('success', 'Logged out.');
    }
}
