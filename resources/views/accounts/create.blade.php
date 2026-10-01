@extends('layouts.app', ['title' => 'Tambah Sumber Kas'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="pb-4 border-b border-slate-800">
        <a href="{{ route('accounts.index') }}" class="text-xs text-slate-400 hover:text-amber-400 transition-colors">&larr; Kembali ke Daftar Sumber Kas</a>
        <h2 class="text-xl font-bold text-white tracking-tight mt-2">Tambah Sumber Kas Baru</h2>
        <p class="text-xs text-slate-400 mt-1">Daftarkan rekening bank operasional atau kas tunai perusahaan.</p>
    </div>

    <form method="POST" action="{{ route('accounts.store') }}" class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Nama Sumber Kas / Rekening</label>
            <input type="text" id="name" name="name" required value="{{ old('name') }}"
                placeholder="Contoh: Rekening BCA Operasional, Kas Tunai Brankas"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
        </div>

        <div>
            <label for="initial_balance" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Saldo Awal (Rp)</label>
            <input type="number" step="0.01" min="0" id="initial_balance" name="initial_balance" required value="{{ old('initial_balance', 0) }}"
                placeholder="0"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
            <p class="text-[11px] text-slate-400 mt-1">Saldo riil awal saat rekening ini pertama kali dimasukkan ke sistem.</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="{{ route('accounts.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                Batal
            </a>
            <button type="submit"
                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
                Simpan Sumber Kas
            </button>
        </div>
    </form>
</div>
@endsection
