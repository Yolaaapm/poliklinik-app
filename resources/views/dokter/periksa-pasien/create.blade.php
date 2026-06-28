<x-layouts.app title="Periksa Pasien">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('periksa-pasien.index') }}" class="inline-flex items-center justify-center w-9 h-9 
                  rounded-lg bg-slate-100 text-slate-500 
                  hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
        </h2>
    </div>

    {{-- Notifikasi Error/Handling Stok Habis dari Controller --}}
    @if(session('error'))
        <div class="p-4 mb-5 text-sm text-red-800 rounded-lg bg-red-50 font-semibold border border-red-200">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Card --}}
    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            <form action="{{ route('periksa-pasien.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_daftar_poli" value="{{ $id }}">

                {{-- Pilih Obat --}}
                <div class="form-control mb-5">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Pilih Obat <span class="text-slate-400 font-normal">(Bisa multi-obat)</span></span>
                    </label>
                    <select id="select-obat" class="select select-bordered w-full rounded-lg border-2 px-4 focus:border-primary focus:outline-none">
                        <option value="">-- Pilih Obat --</option>
                        @foreach ($obats as $obat)
                            @if($obat->stok == 0)
                                {{-- Handling Stok Habis: di-disabled agar tidak bisa dipilih dokter --}}
                                <option value="{{ $obat->id }}" disabled class="text-red-500 font-bold bg-red-50">
                                    [STOK HABIS] {{ $obat->nama_obat }} — Rp{{ number_format($obat->harga) }}
                                </option>
                            @elseif($obat->stok < 5)
                                {{-- Indikator Stok Menipis (di bawah 5) --}}
                                <option value="{{ $obat->id }}"
                                    data-nama="{{ $obat->nama_obat }}"
                                    data-harga="{{ $obat->harga }}"
                                    class="text-amber-600 font-semibold bg-amber-50">
                                    [STOK MENIPIS: {{ $obat->stok }}] {{ $obat->nama_obat }} — Rp{{ number_format($obat->harga) }}
                                </option>
                            @else
                                {{-- Stok Aman --}}
                                <option value="{{ $obat->id }}"
                                    data-nama="{{ $obat->nama_obat }}"
                                    data-harga="{{ $obat->harga }}"
                                    class="text-green-600">
                                    (Sisa: {{ $obat->stok }}) {{ $obat->nama_obat }} — Rp{{ number_format($obat->harga) }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Obat Terpilih --}}
                <div class="form-control mb-5">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Obat Terpilih</span>
                    </label>

                    <ul id="obat-terpilih" class="flex flex-col gap-2 mb-2 min-h-[48px]">
                        </ul>

                    {{-- Data input yang dikirim ke controller --}}
                    <input type="hidden" name="biaya_periksa" id="biaya_periksa" value="150000">
                    <input type="hidden" name="obat_json" id="obat_json">
                </div>

                {{-- Rincian Biaya & Total Harga --}}
                <div class="form-control mb-5">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Total Biaya Periksa (Jasa Dokter Rp150.000 + Obat)</span>
                    </label>
                    <div class="input input-bordered w-full rounded-lg flex items-center bg-slate-50 text-slate-800 font-bold text-lg border-2 px-4" id="total-harga">
                        Rp 150,000
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="form-control mb-8">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Catatan Periksa / Resep / Keluhan <span class="text-red-500">*</span></span>
                    </label>
                    <textarea name="catatan" id="catatan" rows="4" required
                        placeholder="Masukkan catatan hasil pemeriksaan pasien dan aturan minum obat..."
                        class="textarea textarea-bordered w-full border-2 px-4 py-2 rounded-lg resize-none focus:border-primary focus:outline-none">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3">
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-sm transition">
                        <i class="fas fa-save mr-1"></i>
                        Simpan & Selesai Periksa
                    </button>
                    <a href="{{ route('periksa-pasien.index') }}"
                        class="px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm transition">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script>
        const selectObat = document.getElementById('select-obat');
        const listObat = document.getElementById('obat-terpilih');
        const inputBiaya = document.getElementById('biaya_periksa');
        const inputObatJson = document.getElementById('obat_json');
        const totalHargaEl = document.getElementById('total-harga');

        // Konstanta biaya jasa dokter tetap sesuai ketentuan
        const BIAYA_DOKTER = 150000;
        let daftarObat = [];

        selectObat.addEventListener('change', () => {
            const selectedOption = selectObat.options[selectObat.selectedIndex];
            const id = selectedOption.value;
            const nama = selectedOption.dataset.nama;
            const harga = parseInt(selectedOption.dataset.harga || 0);

            if (!id || daftarObat.some(o => o.id == id)) return;

            daftarObat.push({ id, nama, harga });
            renderObat();
            selectObat.selectedIndex = 0; // Reset select dropdown ke default
        });

        function renderObat() {
            listObat.innerHTML = '';
            let totalHargaObat = 0;

            daftarObat.forEach((obat, index) => {
                totalHargaObat += obat.harga;

                const item = document.createElement('li');
                item.className = 'flex items-center justify-between px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 shadow-sm';
                item.innerHTML = `
                    <div class="flex items-center gap-2">
                        <i class="fas fa-pill text-primary text-xs"></i>
                        <span>${obat.nama} — <span class="font-semibold">Rp ${obat.harga.toLocaleString('id-ID')}</span></span>
                    </div>
                    <button type="button"
                        onclick="hapusObat(${index})"
                        class="flex items-center justify-center w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-lg transition border-none shadow">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                `;
                listObat.appendChild(item);
            });

            // Total Biaya Akhir = Jasa Dokter (150.000) + Total Harga Semua Obat Terpilih
            const totalBiayaAkhir = BIAYA_DOKTER + totalHargaObat;

            inputBiaya.value = totalBiayaAkhir;
            totalHargaEl.textContent = `Rp ${totalBiayaAkhir.toLocaleString('id-ID')}`;
            inputObatJson.value = JSON.stringify(daftarObat.map(o => o.id));
        }

        function hapusObat(index) {
            daftarObat.splice(index, 1);
            renderObat();
        }
    </script>

</x-layouts.app>