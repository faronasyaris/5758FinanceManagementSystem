@extends('layouts.app', ['title' => 'Ubah Sumber Kas'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="pb-4 border-b border-slate-800">
        <a href="{{ route('accounts.index') }}" class="text-xs text-slate-400 hover:text-amber-400 transition-colors">&larr; Kembali ke Daftar Sumber Kas</a>
        <h2 class="text-xl font-bold text-white tracking-tight mt-2">Ubah Sumber Kas</h2>
    </div>

    <form method="POST" action="{{ route('accounts.update', $account) }}" class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Nama Sumber Kas / Rekening</label>
            <input type="text" id="name" name="name" required value="{{ old('name', $account->name) }}"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
        </div>

        <div class="p-3.5 rounded-xl bg-slate-800/50 border border-slate-700/60 text-xs text-slate-400">
            <div class="flex justify-between py-1">
                <span>Saldo Awal:</span>
                <span class="font-bold text-white">Rp {{ number_format($account->initial_balance, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between py-1">
                <span>Saldo Riil Saat Ini:</span>
                <span class="font-bold text-amber-400">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Saldo berjalan dihitung otomatis berdasarkan mutasi transaksi lunas.</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="{{ route('accounts.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                Batal
            </a>
            <button type="submit"
                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
