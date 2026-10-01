@extends('layouts.app', ['title' => 'Tambah Klien'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="pb-4 border-b border-slate-800">
        <a href="{{ route('clients.index') }}" class="text-xs text-slate-400 hover:text-amber-400 transition-colors">&larr; Kembali ke Daftar Klien</a>
        <h2 class="text-xl font-bold text-white tracking-tight mt-2">Tambah Klien Baru</h2>
    </div>

    <form method="POST" action="{{ route('clients.store') }}" class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Nama Klien / Perusahaan / Instansi</label>
            <input type="text" id="name" name="name" required value="{{ old('name') }}"
                placeholder="Contoh: Dinas PU Bina Marga, PT Sumber Makmur"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
        </div>

        <div>
            <label for="contact_info" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Kontak / Telepon / WhatsApp / PIC</label>
            <input type="text" id="contact_info" name="contact_info" value="{{ old('contact_info') }}"
                placeholder="Contoh: 0812-3456-7890 (Ibu Maya)"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
        </div>

        <div>
            <label for="address" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Alamat Kantor / Domisili</label>
            <textarea id="address" name="address" rows="3"
                placeholder="Contoh: Gedung Menara Hijau Lt. 5, Jl. MT Haryono No. 33"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">{{ old('address') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="{{ route('clients.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                Batal
            </a>
            <button type="submit"
                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
                Simpan Klien
            </button>
        </div>
    </form>
</div>
@endsection
