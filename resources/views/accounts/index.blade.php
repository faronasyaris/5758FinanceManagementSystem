@extends('layouts.app', ['title' => 'Sumber Kas'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Sumber Kas & Multi-Rekening</h2>
            <p class="text-xs text-slate-400 mt-1">
                Kelola rekening bank operasional dan kas tunai perusahaan dengan pencatatan saldo awal & saldo berjalan.
            </p>
        </div>
        <a href="{{ route('accounts.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Tambah Sumber Kas</span>
        </a>
    </div>

    <!-- Summary Box -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Kas Riil Saat Ini</span>
                <div class="text-2xl font-black text-amber-400 tracking-tight mt-1">
                    Rp {{ number_format($totalCurrentBalance, 0, ',', '.') }}
                </div>
            </div>
            <div class="p-3 rounded-xl bg-amber-400/10 text-amber-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Saldo Awal Terdaftar</span>
                <div class="text-2xl font-black text-slate-200 tracking-tight mt-1">
                    Rp {{ number_format($totalInitialBalance, 0, ',', '.') }}
                </div>
            </div>
            <div class="p-3 rounded-xl bg-slate-800 text-slate-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Table of Accounts -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6">Nama Sumber Kas / Rekening</th>
                        <th class="py-3.5 px-6 text-right">Saldo Awal</th>
                        <th class="py-3.5 px-6 text-right">Saldo Riil Saat Ini</th>
                        <th class="py-3.5 px-6 text-center">Jumlah Transaksi</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($accounts as $account)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6 font-bold text-white">
                                {{ $account->name }}
                            </td>
                            <td class="py-4 px-6 text-right text-slate-300">
                                Rp {{ number_format($account->initial_balance, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right font-black text-amber-400 text-sm">
                                Rp {{ number_format($account->current_balance, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-center text-slate-400">
                                {{ $account->transactions_count }} transaksi
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <form method="POST" action="{{ route('accounts.recalculate', $account) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" title="Sinkronkan Ulang Saldo Riil"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                                        Sync Saldo
                                    </button>
                                </form>
                                <a href="{{ route('accounts.edit', $account) }}"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-amber-400/10 hover:bg-amber-400/20 text-amber-300 border border-amber-400/30 transition-colors">
                                    Ubah
                                </a>
                                @if ($account->transactions_count === 0)
                                    <form method="POST" action="{{ route('accounts.destroy', $account) }}" class="inline-block" onsubmit="return confirm('Hapus sumber kas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-500">
                                Belum ada sumber kas yang didaftarkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
