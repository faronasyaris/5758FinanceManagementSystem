@extends('layouts.app', ['title' => 'Daftar Klien'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Daftar Klien & Instansi</h2>
            <p class="text-xs text-slate-400 mt-1">
                Data klien/instansi rekanan proyek untuk dropdown transaksi dan monitoring piutang.
            </p>
        </div>
        <a href="{{ route('clients.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Tambah Klien Baru</span>
        </a>
    </div>

    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6">Nama Klien / Perusahaan</th>
                        <th class="py-3.5 px-6">Kontak / PIC</th>
                        <th class="py-3.5 px-6">Alamat</th>
                        <th class="py-3.5 px-6 text-center">Jumlah Proyek</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($clients as $client)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6 font-bold text-white">
                                {{ $client->name }}
                            </td>
                            <td class="py-4 px-6 text-slate-300">
                                {{ $client->contact_info ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-slate-400 truncate max-w-xs">
                                {{ $client->address ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-center text-amber-400 font-bold">
                                {{ $client->projects_count }} Proyek
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('clients.edit', $client) }}"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-amber-400/10 hover:bg-amber-400/20 text-amber-300 border border-amber-400/30 transition-colors">
                                    Ubah
                                </a>
                                @if ($client->projects_count === 0)
                                    <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline-block" onsubmit="return confirm('Hapus data klien ini?')">
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
                                Belum ada klien yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
