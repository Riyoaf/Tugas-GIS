<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RuasJalanController extends Controller
{
    public function create()
    {
        $token = session('api_token');

        if (!$token) {
            Log::error('Token API tidak ditemukan di session');
            return back()->with('error', 'Token API tidak ditemukan. Silakan login kembali.');
        }

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        $eksisting = [];
        $jenisJalan = [];
        $kondisi = [];

        try {
            // 1. Eksisting
            $eksistingResponse = Http::withHeaders($headers)->get('https://gisapis.manpits.xyz/api/meksisting');
            if ($eksistingResponse->successful()) {
                $data = $eksistingResponse->json();
                $eksisting = $data['eksisting'] ?? [];
                Log::info('Eksisting Loaded: ' . count($eksisting));
            }

            // 2. Jenis Jalan
            $jenisResponse = Http::withHeaders($headers)->get('https://gisapis.manpits.xyz/api/mjenisjalan');
            if ($jenisResponse->successful()) {
                $data = $jenisResponse->json();
                $jenisJalan = $data['eksisting'] ?? []; // perhatikan: key tetap 'eksisting'
                Log::info('Jenis Jalan Loaded: ' . count($jenisJalan));
            }

            // 3. Kondisi
            $kondisiResponse = Http::withHeaders($headers)->get('https://gisapis.manpits.xyz/api/mkondisi');
            if ($kondisiResponse->successful()) {
                $data = $kondisiResponse->json();
                $kondisi = $data['eksisting'] ?? [];
                Log::info('Kondisi Loaded: ' . count($kondisi));
            }
        } catch (\Exception $e) {
            Log::error('Gagal memuat data master: ' . $e->getMessage());
        }

        return view('layouts.add_ruasjalan', [
            'eksistingOptions' => $eksisting,
            'jenisJalanOptions' => $jenisJalan,
            'kondisiOptions' => $kondisi
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'paths' => 'required|string',
            'desa_id' => 'required|numeric',
            'kode_ruas' => 'required|string',
            'nama_ruas' => 'required|string',
            'panjang' => 'required|numeric',
            'lebar' => 'required|numeric',
            'eksisting_id' => 'required|numeric',
            'kondisi_id' => 'required|numeric',
            'jenisjalan_id' => 'required|numeric',
            'keterangan' => 'nullable|string'
        ]);

        $token = session('api_token');

        if (!$token) {
            return back()->with('error', 'Token API tidak ditemukan. Silakan login kembali.');
        }

        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])
                ->asForm()
                ->post('https://gisapis.manpits.xyz/api/ruasjalan', [
                    'paths' => $request->paths,
                    'desa_id' => $request->desa_id,
                    'kode_ruas' => $request->kode_ruas,
                    'nama_ruas' => $request->nama_ruas,
                    'panjang' => $request->panjang,
                    'lebar' => $request->lebar,
                    'eksisting_id' => $request->eksisting_id,
                    'kondisi_id' => $request->kondisi_id,
                    'jenisjalan_id' => $request->jenisjalan_id,
                    'keterangan' => $request->keterangan
                ]);

            if ($response->successful()) {
                return redirect()->route('map.show')->with('success', 'Ruas jalan berhasil disimpan.');
            }

            Log::error('Gagal menyimpan ruas jalan:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return back()->with('error', 'Gagal menyimpan ruas jalan: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Exception saat menyimpan ruas jalan:', [
                'message' => $e->getMessage()
            ]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // public function getAll()
    // {
    //     $token = session('api_token');

    //     if (!$token) {
    //         return response()->json(['error' => 'Token tidak ditemukan'], 401);
    //     }

    //     try {
    //         $response = Http::timeout(30)->withHeaders([
    //             'Authorization' => 'Bearer ' . $token,
    //             'Accept' => 'application/json',
    //         ])->get('https://gisapis.manpits.xyz/api/ruasjalan');


    //         if ($response->successful()) {
    //             $data = $response->json();
    //             return response()->json($data['data'] ?? []); // ✅ Ambil array dalam key 'data'
    //         }

    //         Log::error('Gagal mengambil data ruas jalan:', [
    //             'status' => $response->status(),
    //             'body' => $response->body()
    //         ]);

    //         return response()->json(['error' => 'Gagal mengambil data ruas jalan'], 500);
    //     } catch (\Exception $e) {
    //         Log::error('Exception saat mengambil data ruas jalan:', [
    //             'message' => $e->getMessage()
    //         ]);

    //         return response()->json(['error' => 'Terjadi kesalahan server'], 500);
    //     }
    // }

    // public function getAll()
    // {
    //     $token = session('api_token');

    //     if (!$token) {
    //         return response()->json(['error' => 'Token tidak ditemukan'], 401);
    //     }

    //     try {
    //         $response = Http::timeout(30)->withHeaders([
    //             'Authorization' => 'Bearer ' . $token,
    //             'Accept' => 'application/json',
    //         ])->get('https://gisapis.manpits.xyz/api/ruasjalan');

    //         // Debug response
    //         Log::info('API Response Status: ' . $response->status());
    //         Log::info('API Response Body: ' . $response->body());

    //         if ($response->successful()) {
    //             $data = $response->json();

    //             // Debug struktur data
    //             Log::info('Response Structure: ', $data);

    //             // Coba berbagai kemungkinan struktur
    //             if (isset($data['data'])) {
    //                 Log::info('Found data in "data" key');
    //                 return response()->json($data['data']);
    //             } elseif (isset($data['ruasjalan'])) {
    //                 Log::info('Found data in "ruasjalan" key');
    //                 return response()->json($data['ruasjalan']);
    //             } else {
    //                 Log::info('Returning raw data');
    //                 return response()->json($data);
    //             }
    //         }

    //         return response()->json(['error' => 'Gagal mengambil data ruas jalan'], 500);
    //     } catch (\Exception $e) {
    //         Log::error('Exception: ' . $e->getMessage());
    //         return response()->json(['error' => 'Terjadi kesalahan server'], 500);
    //     }
    // }

    public function getAll()
    {
        $token = session('api_token');

        if (!$token) {
            return response()->json(['error' => 'Token tidak ditemukan'], 401);
        }

        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get('https://gisapis.manpits.xyz/api/ruasjalan');

            if ($response->successful()) {
                $responseData = $response->json();

                // Debug log
                Log::info('API getAll Response:', $responseData);

                // Ambil data sesuai struktur response API
                $data = [];
                if (isset($responseData['data'])) {
                    $data = $responseData['data'];
                } elseif (isset($responseData['ruasjalan'])) {
                    $data = $responseData['ruasjalan'];
                } elseif (is_array($responseData)) {
                    $data = $responseData;
                }

                Log::info('Processed data for frontend:', ['count' => count($data)]);

                return response()->json($data);
            }

            Log::error('Gagal mengambil data ruas jalan:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json(['error' => 'Gagal mengambil data ruas jalan'], 500);
        } catch (\Exception $e) {
            Log::error('Exception saat mengambil data ruas jalan:', [
                'message' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Terjadi kesalahan server'], 500);
        }
    }

    // Method tambahan untuk debugging - bisa dihapus setelah masalah teratasi
    public function testApi()
    {
        $token = session('api_token');

        if (!$token) {
            return response()->json(['error' => 'Token tidak ditemukan']);
        }

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        $results = [];

        try {
            // Test eksisting API
            $eksistingResponse = Http::timeout(30)->withHeaders($headers)->get('https://gisapis.manpits.xyz/api/meksisting');
            $results['eksisting'] = [
                'status' => $eksistingResponse->status(),
                'successful' => $eksistingResponse->successful(),
                'data' => $eksistingResponse->json()
            ];

            // Test jenis jalan API
            $jenisJalanResponse = Http::timeout(30)->withHeaders($headers)->get('https://gisapis.manpits.xyz/api/mjenisjalan');
            $results['jenisjalan'] = [
                'status' => $jenisJalanResponse->status(),
                'successful' => $jenisJalanResponse->successful(),
                'data' => $jenisJalanResponse->json()
            ];

            // Test kondisi API
            $kondisiResponse = Http::timeout(30)->withHeaders($headers)->get('https://gisapis.manpits.xyz/api/mkondisi');
            $results['kondisi'] = [
                'status' => $kondisiResponse->status(),
                'successful' => $kondisiResponse->successful(),
                'data' => $kondisiResponse->json()
            ];
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }

        return response()->json($results);
    }

    public function debugRuas()
    {
        $token = session('api_token'); // pastikan token tersedia di session
        if (!$token) return 'Token tidak ditemukan di session';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('https://gisapis.manpits.xyz/api/ruasjalan');

        return $response->json(); // langsung tampilkan JSON di browser
    }

    public function tabel()
    {
        $token = session('api_token');
        if (!$token) {
            return redirect()->route('login')->with('error', 'Token tidak ditemukan.');
        }

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        try {
            // 1. Ambil data ruas jalan
            $response = Http::timeout(30)->withHeaders($headers)->get('https://gisapis.manpits.xyz/api/ruasjalan');

            if (!$response->successful()) {
                Log::error('API Error - Status: ' . $response->status() . ', Body: ' . $response->body());
                return back()->with('error', 'Gagal mengambil data ruas jalan. Status: ' . $response->status());
            }

            $responseData = $response->json();

            // Ambil data ruas jalan
            $ruasjalan = [];
            if (isset($responseData['data'])) {
                $ruasjalan = $responseData['data'];
            } elseif (isset($responseData['ruasjalan'])) {
                $ruasjalan = $responseData['ruasjalan'];
            } elseif (is_array($responseData)) {
                $ruasjalan = $responseData;
            }

            // 2. Ambil data master untuk referensi
            $eksistingData = [];
            $kondisiData = [];
            $jenisJalanData = [];

            // Ambil data eksisting
            try {
                $eksistingResponse = Http::timeout(30)->withHeaders($headers)->get('https://gisapis.manpits.xyz/api/meksisting');
                if ($eksistingResponse->successful()) {
                    $eksistingResult = $eksistingResponse->json();
                    $eksistingData = $eksistingResult['eksisting'] ?? [];
                }
            } catch (\Exception $e) {
                Log::warning('Gagal mengambil data eksisting: ' . $e->getMessage());
            }

            // Ambil data kondisi
            try {
                $kondisiResponse = Http::timeout(30)->withHeaders($headers)->get('https://gisapis.manpits.xyz/api/mkondisi');
                if ($kondisiResponse->successful()) {
                    $kondisiResult = $kondisiResponse->json();
                    $kondisiData = $kondisiResult['eksisting'] ?? [];
                }
            } catch (\Exception $e) {
                Log::warning('Gagal mengambil data kondisi: ' . $e->getMessage());
            }

            // Ambil data jenis jalan
            try {
                $jenisJalanResponse = Http::timeout(30)->withHeaders($headers)->get('https://gisapis.manpits.xyz/api/mjenisjalan');
                if ($jenisJalanResponse->successful()) {
                    $jenisJalanResult = $jenisJalanResponse->json();
                    $jenisJalanData = $jenisJalanResult['eksisting'] ?? [];
                }
            } catch (\Exception $e) {
                Log::warning('Gagal mengambil data jenis jalan: ' . $e->getMessage());
            }

            // 3. Buat mapping untuk data master (untuk lookup yang cepat)
            $eksistingMap = [];
            foreach ($eksistingData as $item) {
                $eksistingMap[$item['id']] = $item;
            }

            $kondisiMap = [];
            foreach ($kondisiData as $item) {
                $kondisiMap[$item['id']] = $item;
            }

            $jenisJalanMap = [];
            foreach ($jenisJalanData as $item) {
                $jenisJalanMap[$item['id']] = $item;
            }

            // 4. Gabungkan data ruas jalan dengan data master
            foreach ($ruasjalan as &$item) {
                // Tambahkan data eksisting
                if (isset($item['eksisting_id']) && isset($eksistingMap[$item['eksisting_id']])) {
                    $item['meksisting'] = $eksistingMap[$item['eksisting_id']];
                }

                // Tambahkan data kondisi
                if (isset($item['kondisi_id']) && isset($kondisiMap[$item['kondisi_id']])) {
                    $item['mkondisi'] = $kondisiMap[$item['kondisi_id']];
                }

                // Tambahkan data jenis jalan
                if (isset($item['jenisjalan_id']) && isset($jenisJalanMap[$item['jenisjalan_id']])) {
                    $item['mjenisjalan'] = $jenisJalanMap[$item['jenisjalan_id']];
                }
            }

            Log::info('DATA RUAS JALAN SETELAH DIGABUNG:', $ruasjalan);

            return view('layouts.tabel_ruasjalan', compact('ruasjalan'));
        } catch (\Exception $e) {
            Log::error('Exception di tabel(): ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
