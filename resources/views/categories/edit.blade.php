@extends('layouts.app', ['title' => 'Ubah Kategori'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="pb-4 border-b border-slate-800">
        <a href="{{ route('categories.index') }}" class="text-xs text-slate-400 hover:text-amber-400 transition-colors">&larr; Kembali ke Daftar Kategori</a>
        <h2 class="text-xl font-bold text-white tracking-tight mt-2">Ubah Kategori</h2>
    </div>

    <form method="POST" action="{{ route('categories.update', $category) }}" class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Nama Kategori</label>
            <input type="text" id="name" name="name" required value="{{ old('name', $category->name) }}"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Jenis Aliran Dana</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer hover:border-emerald-500/50 has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-500/10 transition-all">
                    <input type="radio" name="type" value="in" required {{ old('type', $category->type) === 'in' ? 'checked' : '' }}
                        class="text-emerald-500 focus:ring-emerald-400">
                    <div>
                        <div class="text-xs font-bold text-emerald-400">Uang Masuk</div>
                        <div class="text-[10px] text-slate-400">Pendapatan, Termin, DP Klien</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer hover:border-rose-500/50 has-[:checked]:border-rose-400 has-[:checked]:bg-rose-500/10 transition-all">
                    <input type="radio" name="type" value="out" required {{ old('type', $category->type) === 'out' ? 'checked' : '' }}
                        class="text-rose-500 focus:ring-rose-400">
                    <div>
                        <div class="text-xs font-bold text-rose-400">Uang Keluar</div>
                        <div class="text-[10px] text-slate-400">Honor, Material, Operasional, Kasbon</div>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="{{ route('categories.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition-colors">
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
