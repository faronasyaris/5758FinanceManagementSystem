@extends('layouts.app', ['title' => 'Proyek & Kegiatan'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Proyek & Kegiatan Perusahaan</h2>
            <p class="text-xs text-slate-400 mt-1">
                Seluruh arus kas dan termin pembayaran di-tagging berbasis proyek untuk kontrol profitabilitas per unit bisnis.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('projections.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Simulasi & Kalkulator Proyek</span>
            </a>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
        <a href="{{ route('projects.index') }}"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ empty($currentStatus) ? 'bg-amber-400 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
            Semua Proyek ({{ $projects->count() }})
        </a>
        <a href="{{ route('projects.index', ['status' => 'draft']) }}"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentStatus === 'draft' ? 'bg-amber-400 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
            Draft / Simulasi
        </a>
        <a href="{{ route('projects.index', ['status' => 'ongoing']) }}"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentStatus === 'ongoing' ? 'bg-amber-400 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
            Ongoing (Berjalan)
        </a>
        <a href="{{ route('projects.index', ['status' => 'completed']) }}"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentStatus === 'completed' ? 'bg-amber-400 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
            Selesai (Completed)
        </a>
    </div>

    <!-- Table of Projects -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6">Nama Proyek / Klien</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Nilai Kontrak</th>
                        <th class="py-3.5 px-6 text-right">Total Uang Masuk</th>
                        <th class="py-3.5 px-6 text-right">Total Pengeluaran</th>
                        <th class="py-3.5 px-6 text-right">Project Margin</th>
                        <th class="py-3.5 px-6 text-center">Indikator</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($projects as $project)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6">
                                <a href="{{ route('projects.show', $project) }}" class="font-bold text-white hover:text-amber-400">
                                    {{ $project->name }}
                                </a>
                                <p class="text-slate-400 text-[11px] mt-0.5">Klien: {{ $project->client ? $project->client->name : '-' }}</p>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($project->status === 'ongoing')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-500/10 text-blue-400 border border-blue-500/20">Ongoing</span>
                                @elseif ($project->status === 'draft')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-500/10 text-slate-400 border border-slate-500/20">Draft</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Selesai</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-medium text-slate-200">
                                @if ($project->projection)
                                    Rp {{ number_format($project->projection->total_contract_value, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-blue-400">
                                Rp {{ number_format($project->total_income, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-rose-400">
                                Rp {{ number_format($project->total_expense, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right font-black {{ $project->project_margin >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                Rp {{ number_format($project->project_margin, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($project->isProfitable())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Surplus
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Defisit
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('projects.show', $project) }}"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 transition-colors">
                                    Rapor Proyek
                                </a>
                                <a href="{{ route('projects.edit', $project) }}"
                                    class="px-2 py-1 rounded-lg text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 px-6 text-center text-slate-500">
                                Belum ada proyek dengan status ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
