@extends('layouts.app', ['title' => 'Buat Proyek'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="pb-4 border-b border-slate-800">
        <a href="{{ route('projects.index') }}" class="text-xs text-slate-400 hover:text-amber-400 transition-colors">&larr; Kembali ke Daftar Proyek</a>
        <h2 class="text-xl font-bold text-white tracking-tight mt-2">Daftarkan Proyek / Kegiatan</h2>
        <p class="text-xs text-slate-400 mt-1">
            Untuk proyek yang membutuhkan kalkulasi Pagu Anggaran & simulasi termin pembayaran, gunakan menu
            <a href="{{ route('projections.create') }}" class="text-amber-400 underline font-semibold">Kalkulator Proyeksi</a>.
        </p>
    </div>

    <form method="POST" action="{{ route('projects.store') }}" class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl space-y-5">
        @csrf

        <div>
            <label for="client_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Pilih Klien / Instansi</label>
            <select id="client_id" name="client_id" required
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
                <option value="">-- Pilih Klien --</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Nama Proyek / Kegiatan</label>
            <input type="text" id="name" name="name" required value="{{ old('name') }}"
                placeholder="Contoh: Pengadaan Genset Gedung B, Renovasi Kantor"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="start_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}"
                    class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
            </div>

            <div>
                <label for="end_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Target Selesai</label>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                    class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
            </div>
        </div>

        <div>
            <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Status Proyek</label>
            <select id="status" name="status" required
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Perencanaan)</option>
                <option value="ongoing" {{ old('status', 'ongoing') === 'ongoing' ? 'selected' : '' }}>Ongoing (Sedang Berjalan)</option>
                <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
            </select>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="{{ route('projects.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                Batal
            </a>
            <button type="submit"
                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
                Simpan Proyek
            </button>
        </div>
    </form>
</div>
@endsection
