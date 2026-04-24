<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dokter = Auth::user();
        $jadwalPeriksas = JadwalPeriksa::where('id_dokter', $dokter->id)->orderBy('hari')->get();
        return view('dokter.jadwal-periksa.index', compact('jadwalPeriksas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dokter.jadwal-periksa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required'
        ]);

        JadwalPeriksa::create([
            'id_dokter' => Auth::id(),
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai
        ]);

        return redirect()->route('jadwal-periksa.index')
            ->with('message', 'Data Berhasil di Simpan')
            ->with('type', 'success');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $jadwalPeriksa = JadwalPeriksa::findOrFail($id);
        return view('dokter.jadwal-periksa.edit', compact('jadwalPeriksa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required'
        ]);

        $jadwalPeriksa = JadwalPeriksa::findOrFail($id);
        $jadwalPeriksa->update([
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai
        ]);

        return redirect()->route('jadwal-periksa.index')
            ->with('message', 'Berhasil Melakukan Update Data')
            ->with('type', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jadwal = JadwalPeriksa::findOrFail($id);
        $jadwal->delete();

        return redirect()->route('jadwal-periksa.index')
            ->with('message', 'Berhasil Melakukan Hapus Data')
            ->with('type', 'success');
    }
}
