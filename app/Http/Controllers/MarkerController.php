<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MarkerController extends Controller
{
    /**
     * Menampilkan halaman peta dengan marker
     */
    public function showMap()
    {
        return view('layouts.V_map');
        // return redirect()->route('map.show');
    }
    
    /**
     * Menampilkan form untuk menambahkan marker baru
     */
    public function create(Request $request)
    {
        return view('layouts.add_marker');
    }
    
    /**
     * Menampilkan form untuk mengedit marker
     */
    public function edit($id)
    {
        $marker = Marker::findOrFail($id);
        return view('layouts.add_marker', compact('marker'));
    }
    
    /**
     * Menyimpan marker baru ke database
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $marker = Marker::create([
            'name' => $request->name,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('map.show')->with('success', 'Marker berhasil ditambahkan');
    }
    
    /**
     * Memperbarui marker yang sudah ada
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $marker = Marker::findOrFail($id);
        $marker->update([
            'name' => $request->name,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('map.show')->with('success', 'Marker berhasil diperbarui');
    }
    
    /**
     * Memperbarui posisi marker (API)
     */
    public function updatePosition(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $marker = Marker::findOrFail($id);
        $marker->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true, 
            'id' => $marker->id,
            'name' => $marker->name,
            'description' => $marker->description,
            'latitude' => $marker->latitude,
            'longitude' => $marker->longitude
        ]);
    }
    
    /**
     * Menghapus marker
     */
    public function destroy($id)
    {
        $marker = Marker::findOrFail($id);
        $marker->delete();

        return response()->json(['success' => true, 'message' => 'Marker berhasil dihapus']);
    }
    
    /**
     * Mendapatkan semua marker (API)
     */
    public function getAllMarkers()
    {
        $markers = Marker::all();
        return response()->json($markers);
    }
}