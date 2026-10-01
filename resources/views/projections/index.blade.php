@extends('layouts.app', ['title' => 'Kalkulator Proyeksi & Auto-Termin'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Kalkulator Proyeksi & Termin Otomatis</h2>
            <p class="text-xs text-slate-400 mt-1">
                Fase perencanaan pintar sebelum proyek berjalan: Kunci Pagu Anggaran dan jadwalkan termin pembayaran otomatis.
            </p>
        </div>
        <a href="{{ route('projections.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Buat Simulasi Proyek Baru</span>
        </a>
    </div>

    <!-- Section 1: Simulasi Draft (Menunggu Eksekusi) -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                <h3 class="text-base font-bold text-white tracking-tight">Proyek Draft / Simulasi (Belum Dijalankan)</h3>
            </div>
            <span class="text-xs text-amber-400 font-semibold">{{ $draftProjects->count() }} Simulasi Aktif</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6">Nama Proyek / Klien</th>
                        <th class="py-3.5 px-6 text-right">Nilai Kontrak</th>
                        <th class="py-3.5 px-6 text-right">Target Keuntungan</th>
                        <th class="py-3.5 px-6 text-right">Pagu Batas Modal</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($draftProjects as $proj)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6">
                                <span class="font-bold text-white">{{ $proj->name }}</span>
                                <p class="text-slate-400 text-[11px] mt-0.5">Klien: {{ $proj->client ? $proj->client->name : '-' }}</p>
                            </td>
                            <td class="py-4 px-6 text-right font-medium text-slate-200">
                                @if ($proj->projection)
                                    Rp {{ number_format($proj->projection->total_contract_value, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-emerald-400">
                                @if ($proj->projection)
                                    Rp {{ number_format($proj->projection->target_profit_nominal, 0, ',', '.') }}
                                    <span class="text-[10px] text-slate-400 font-normal">({{ number_format($proj->projection->target_profit_percentage, 1) }}%)</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-black text-amber-400">
                                @if ($proj->projection)
                                    Rp {{ number_format($proj->projection->estimated_expense, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('projects.show', $proj) }}"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-sm transition-colors">
                                    Lihat & Eksekusi &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-500">
                                Tidak ada proyek draft saat ini. Semua proyek telah dijalankan atau belum dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Proyek Berjalan dengan Proyeksi Aktif -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                <h3 class="text-base font-bold text-white tracking-tight">Proyek Berjalan (Monitoring vs Pagu Anggaran)</h3>
            </div>
            <span class="text-xs text-emerald-400 font-semibold">{{ $activeProjects->count() }} Proyek Berjalan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6">Nama Proyek</th>
                        <th class="py-3.5 px-6 text-right">Nilai Kontrak</th>
                        <th class="py-3.5 px-6 text-right">Pagu Batas Modal</th>
                        <th class="py-3.5 px-6 text-right">Pengeluaran Riil</th>
                        <th class="py-3.5 px-6 text-right">Sisa Pagu Modal</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($activeProjects as $aproj)
                        @php
                            $ceiling = $aproj->projection ? (float) $aproj->projection->estimated_expense : 0;
                            $realExpense = $aproj->realized_expense;
                            $remainingBudget = $ceiling - $realExpense;
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6">
                                <a href="{{ route('projects.show', $aproj) }}" class="font-bold text-white hover:text-amber-400">
                                    {{ $aproj->name }}
                                </a>
                                <p class="text-slate-400 text-[11px] mt-0.5">{{ $aproj->client ? $aproj->client->name : '-' }}</p>
                            </td>
                            <td class="py-4 px-6 text-right text-slate-300">
                                @if ($aproj->projection)
                                    Rp {{ number_format($aproj->projection->total_contract_value, 0, ',', '.') }}
                                @else
                                    Rp {{ number_format($aproj->total_income, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-medium text-amber-300">
                                Rp {{ number_format($ceiling, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-rose-400">
                                Rp {{ number_format($realExpense, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right font-black {{ $remainingBudget >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                Rp {{ number_format($remainingBudget, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('projects.show', $aproj) }}"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors">
                                    Rapor Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-6 text-center text-slate-500">
                                Belum ada proyek ongoing yang berjalan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
