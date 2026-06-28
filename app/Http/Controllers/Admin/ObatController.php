<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $obats = Obat::all();
        return view('admin.obat.index', compact('obats'));  
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.obat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Tambahkan validasi stok wajib diisi, berupa angka integer, dan minimal nilai 0
        $request->validate([
            'nama_obat' => 'required|string',
            'kemasan' => 'required|string',
            'harga' => 'required|integer',
            'stok' => 'required|integer|min:0', 
        ]);

        // 2. Masukkan data stok ke dalam array penyimpanan data baru
        Obat::create([
            'nama_obat' => $request->nama_obat,
            'kemasan' => $request->kemasan,
            'harga' => $request->harga,
            'stok' => $request->stok, 
        ]);

        return redirect()->route('obat.index')->with('message', 'Data Obat Berhasil dibuat.')->with('type', 'success');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $obat = Obat::findOrFail($id);
        return view('admin.obat.edit')->with([
            'obat' => $obat,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Tambahkan validasi stok wajib diisi saat proses pembaruan data obat
        $request->validate([
            'nama_obat' => 'required|string',
            'kemasan' => 'nullable|string',
            'harga' => 'required|integer',
            'stok' => 'required|integer|min:0', 
        ]);

        // 2. Masukkan data stok baru ke dalam baris logika update
        $obat = Obat::findOrFail($id);
        $obat->update([
            'nama_obat' => $request->nama_obat,
            'kemasan' => $request->kemasan,
            'harga' => $request->harga,
            'stok' => $request->stok, 
        ]);

        return redirect()->route('obat.index')->with('message', 'Data Obat Berhasil di Update.')->with('type', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $obat = Obat::findOrFail($id);
        $obat->delete();

        return redirect()->route('obat.index')->with('message', 'Data Obat Berhasil di Hapus.')->with('type', 'success');
    }
}