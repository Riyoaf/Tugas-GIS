<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $token = session('api_token');

        if (!$token) {
            return redirect()->route('login')->withErrors(['error' => 'Token tidak ditemukan. Silakan login kembali.']);
        }

        // Ambil data user dari API eksternal
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('https://gisapis.manpits.xyz/api/user');

        if ($response->successful()) {
            $user = $response->json();

            session(['user' => $user['data']['user']]);

            session()->save();

            Log::info('User data stored in session:', $user);

            // Kirim data ke view
            return view('layouts.v_map', ['user' => $user]);
        }

        return redirect()->route('login')->withErrors(['error' => 'Gagal mengambil data user.']);

        
    }
}