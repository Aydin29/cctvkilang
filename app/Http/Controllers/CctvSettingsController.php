<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cctv;

class CctvSettingsController extends Controller
{
    public function index()
    {
        $cctvs = Cctv::all();
        return view('cctvs.index', compact('cctvs'));
    }

    public function create()
    {
        return view('cctvs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:cctvs,ip_address',
            'port' => 'nullable|integer',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'playlist_url' => 'nullable|url',
            'streaming_url' => 'nullable|url',
            'status' => 'required|in:active,inactive',
        ]);

        Cctv::create($request->all());

        return redirect()->route('cctvs.index')
                        ->with('success', 'CCTV berhasil ditambahkan.');
    }

    public function edit(Cctv $cctv)
    {
        return view('cctvs.edit', compact('cctv'));
    }

    public function update(Request $request, Cctv $cctv)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:cctvs,ip_address,'.$cctv->id,
            'port' => 'nullable|integer',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'playlist_url' => 'nullable|url',
            'streaming_url' => 'nullable|url',
            'status' => 'required|in:active,inactive',
        ]);

        $cctv->update($request->all());

        return redirect()->route('cctvs.index')
                        ->with('success', 'CCTV berhasil diperbarui.');
    }

    public function destroy(Cctv $cctv)
    {
        $cctv->delete();

        return redirect()->route('cctvs.index')
                        ->with('success', 'CCTV berhasil dihapus.');
    }
}