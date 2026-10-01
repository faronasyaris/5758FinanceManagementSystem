@extends('layouts.app', ['title' => 'Kategori Transaksi'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Kategori Transaksi</h2>
            <p class="text-xs text-slate-400 mt-1">
                Kategori terstruktur tanpa istilah Debit/Kredit: Hanya "Uang Masuk" dan "Uang Keluar".
            </p>
        </div>
        <a href="{{ route('categories.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Tambah Kategori</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Kategori Uang Masuk -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
            <div class="p-4 bg-emerald-500/10 border-b border-emerald-500/20 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-300">Kategori Uang Masuk (Income)</h3>
                </div>
                <span class="text-xs font-bold text-emerald-400">{{ $incomeCategories->count() }} Kategori</span>
            </div>

            <div class="divide-y divide-slate-800/80">
                @forelse ($incomeCategories as $cat)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-800/40 transition-colors">
                        <div>
                            <p class="text-xs font-bold text-white">{{ $cat->name }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $cat->transactions_count }} transaksi tercatat</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('categories.edit', $cat) }}"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-amber-400/10 hover:bg-amber-400/20 text-amber-300 border border-amber-400/30 transition-colors">
                                Ubah
                            </a>
                            @if ($cat->transactions_count === 0)
                                <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-500">Belum ada kategori uang masuk.</div>
                @endforelse
            </div>
        </div>

        <!-- Kategori Uang Keluar -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
            <div class="p-4 bg-rose-500/10 border-b border-rose-500/20 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-rose-300">Kategori Uang Keluar (Expense)</h3>
                </div>
                <span class="text-xs font-bold text-rose-400">{{ $expenseCategories->count() }} Kategori</span>
            </div>

            <div class="divide-y divide-slate-800/80">
                @forelse ($expenseCategories as $cat)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-800/40 transition-colors">
                        <div>
                            <p class="text-xs font-bold text-white">{{ $cat->name }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $cat->transactions_count }} transaksi tercatat</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('categories.edit', $cat) }}"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-amber-400/10 hover:bg-amber-400/20 text-amber-300 border border-amber-400/30 transition-colors">
                                Ubah
                            </a>
                            @if ($cat->transactions_count === 0)
                                <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-500">Belum ada kategori uang keluar.</div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
