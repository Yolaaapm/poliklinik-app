<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriksaPasienController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $obats = Obat::all();
        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_daftar_poli' => 'required',
            'obat_json' => 'required',
            'catatan' => 'nullable|string',
            'biaya_periksa' => 'required|integer',
        ]);

        $obatIds = json_decode($request->obat_json, true);

        if (!empty($obatIds)) {
            foreach ($obatIds as $idObat) {
                $obat = Obat::find($idObat);
                
                if (!$obat || $obat->stok <= 0) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Gagal menyimpan! Stok obat '{$obat->nama_obat}' sudah habis.");
                }
            }
        }

        $periksa = Periksa::create([
            'id_daftar_poli' => $request->id_daftar_poli,
            'tgl_periksa' => now(),
            'catatan' => $request->catatan,
            'biaya_periksa' => $request->biaya_periksa + 150000,
        ]);

        if (!empty($obatIds)) {
            foreach ($obatIds as $idObat) {
                // Simpan detail resep obat pasien
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $idObat,
                ]);

                $obat = Obat::find($idObat);
                $obat->decrement('stok', 1); 
            }
        }

        return redirect()->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan dan stok obat otomatis dikurangi.');
    }
}