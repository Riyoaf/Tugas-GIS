<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $token = session('api_token');

        // Panggil API untuk mendapatkan semua region
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('https://gisapis.manpits.xyz/api/mregion');

        $regions = $response->successful() ? $response->json() : [];

        Log::info('Regions:', $regions);

        Log::info('Controller ini dijalankan: RegionController@index');

        // Tangkap region_id dari request GET (dropdown)
        $selectedRegionId = $request->input('region_id');
        $selectedRegion = null;

        // Cari region berdasarkan ID yang dipilih
        if ($selectedRegionId && is_array($regions)) {
            $selectedRegion = collect($regions)->firstWhere('id', $selectedRegionId);

            if (!is_array($selectedRegion)) {
                $selectedRegion = null;
            }
            
        }

        // Kirim ke view: semua region & region yang dipilih
        return view('layouts.V_map', compact('regions', 'selectedRegion'));
    }
}
