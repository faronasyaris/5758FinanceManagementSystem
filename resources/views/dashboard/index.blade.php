@extends('layouts.app', ['title' => 'Dasbor Eksekutif'])

@section('content')
<div class="space-y-6">

    <!-- Header & Global Timeframe Filter -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-white tracking-tight">Dasbor Eksekutif 5758inc</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-400/10 text-amber-400 border border-amber-400/20">
                    Pusat Komando Finansial
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Visibilitas instan kesehatan arus kas dan proyeksi kegiatan tanpa jargon akuntansi.
            </p>
        </div>

        <!-- Filter Controls -->
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
            <select name="timeframe" onchange="this.form.submit()"
                class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white focus:border-amber-400 focus:outline-none">
                <option value="bulanan" {{ $timeframe === 'bulanan' ? 'selected' : '' }}>Filter: Bulanan</option>
                <option value="triwulan" {{ $timeframe === 'triwulan' ? 'selected' : '' }}>Filter: Triwulan (Kuartal)</option>
                <option value="semester" {{ $timeframe === 'semester' ? 'selected' : '' }}>Filter: Semester</option>
                <option value="tahunan" {{ $timeframe === 'tahunan' ? 'selected' : '' }}>Filter: Tahunan</option>
                <option value="all" {{ $timeframe === 'all' ? 'selected' : '' }}>Filter: Seluruh Periode</option>
            </select>

            @if ($timeframe === 'bulanan')
                <select name="month" onchange="this.form.submit()"
                    class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-slate-200 focus:border-amber-400 focus:outline-none">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            @endif

            @if ($timeframe === 'triwulan')
                <select name="quarter" onchange="this.form.submit()"
                    class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-slate-200 focus:border-amber-400 focus:outline-none">
                    <option value="1" {{ $quarter === 1 ? 'selected' : '' }}>Q1 (Jan - Mar)</option>
                    <option value="2" {{ $quarter === 2 ? 'selected' : '' }}>Q2 (Apr - Jun)</option>
                    <option value="3" {{ $quarter === 3 ? 'selected' : '' }}>Q3 (Jul - Sep)</option>
                    <option value="4" {{ $quarter === 4 ? 'selected' : '' }}>Q4 (Okt - Des)</option>
                </select>
            @endif

            @if ($timeframe === 'semester')
                <select name="semester" onchange="this.form.submit()"
                    class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-slate-200 focus:border-amber-400 focus:outline-none">
                    <option value="1" {{ $semester === 1 ? 'selected' : '' }}>Semester 1 (Jan - Jun)</option>
                    <option value="2" {{ $semester === 2 ? 'selected' : '' }}>Semester 2 (Jul - Des)</option>
                </select>
            @endif

            @if ($timeframe !== 'all')
                <select name="year" onchange="this.form.submit()"
                    class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-slate-200 focus:border-amber-400 focus:outline-none">
                    @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            @endif
        </form>
    </div>

    <!-- OVERDUE RADAR ALERTS (Notifikasi Jatuh Tempo Terlewati) -->
    @if ($overdueAlerts->count() > 0)
        <div class="p-4 rounded-2xl bg-rose-500/10 border-2 border-rose-500/40 shadow-lg shadow-rose-950/20">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    </span>
                    <h3 class="text-sm font-bold text-rose-400 tracking-wide uppercase">
                        Radar Peringatan Overdue ({{ $overdueAlerts->count() }} Tagihan Melewati Jatuh Tempo)
                    </h3>
                </div>
                <span class="text-xs text-rose-300 font-semibold">Tindakan Penagihan / Pelunasan Diperlukan</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($overdueAlerts as $alert)
                    <div class="p-3 rounded-xl bg-slate-900/90 border border-rose-500/30 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $alert->type === 'in' ? 'bg-amber-400/20 text-amber-300' : 'bg-rose-400/20 text-rose-300' }}">
                                    {{ $alert->type === 'in' ? 'Piutang Klien' : 'Kasbon/Hutang' }}
                                </span>
                                <span class="text-xs font-bold text-white truncate max-w-[150px]">{{ $alert->project ? $alert->project->name : '-' }}</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1 truncate">{{ $alert->description ?? $alert->category->name }}</p>
                            <p class="text-[11px] text-rose-400 font-semibold mt-0.5">
                                Jatuh tempo: {{ $alert->due_date->translatedFormat('d M Y') }} ({{ $alert->due_date->diffForHumans() }})
                            </p>
                        </div>
                        <div class="text-right pl-3">
                            <div class="text-xs font-bold text-white">Rp {{ number_format($alert->nominal, 0, ',', '.') }}</div>
                            <a href="{{ route('transactions.index', ['overdue' => 1]) }}"
                                class="inline-block mt-1 text-[11px] font-semibold text-amber-400 hover:text-amber-300 underline">
                                Tangani &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- TOP CARDS (Tiga Pilar Finansial) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Card 1: Total Saldo Saat Ini (Uang Riil) -->
        <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-900/80 border border-slate-800 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400">Total Saldo Saat Ini (Kas Riil)</span>
                    <span class="p-2 rounded-xl bg-amber-400/10 text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                </div>
                <div class="text-2xl lg:text-3xl font-black text-amber-400 tracking-tight mt-3">
                    Rp {{ number_format($totalCurrentCash, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Uang riil yang siap digunakan di seluruh rekening.</p>
            </div>

            <!-- Itemized Accounts Breakdown -->
            <div class="mt-5 pt-4 border-t border-slate-800/80">
                <div class="text-[11px] uppercase font-bold tracking-wider text-slate-400 mb-2">Rincian Sumber Kas:</div>
                <div class="space-y-1.5 max-h-32 overflow-y-auto pr-1">
                    @forelse ($accounts as $acc)
                        <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-slate-800/40 border border-slate-800/60">
                            <span class="text-slate-300 font-medium truncate">{{ $acc->name }}</span>
                            <span class="font-bold text-white">Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <span class="text-xs text-slate-500 italic">Belum ada akun rekening kas terdaftar.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Card 2: Total Piutang (Uang di Luar) -->
        <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-900/80 border border-slate-800 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400">Total Piutang (Uang di Luar)</span>
                    <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </span>
                </div>
                <div class="text-2xl lg:text-3xl font-black text-blue-400 tracking-tight mt-3">
                    Rp {{ number_format($totalPendingIncome, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Tagihan termin klien yang belum cair/diterima.</p>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs">
                <span class="text-slate-400">Uang Masuk Riil ({{ $periodLabel }}):</span>
                <span class="font-bold text-emerald-400">Rp {{ number_format($periodRealizedIn, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Card 3: Total Hutang / Kasbon (Kewajiban) -->
        <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-900/80 border border-slate-800 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400">Total Hutang & Kasbon</span>
                    <span class="p-2 rounded-xl bg-rose-500/10 text-rose-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                    </span>
                </div>
                <div class="text-2xl lg:text-3xl font-black text-rose-400 tracking-tight mt-3">
                    Rp {{ number_format($totalPendingExpense, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-400 mt-1">Tagihan vendor & kasbon karyawan yang belum diselesaikan.</p>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs">
                <span class="text-slate-400">Uang Keluar Riil ({{ $periodLabel }}):</span>
                <span class="font-bold text-rose-400">Rp {{ number_format($periodRealizedOut, 0, ',', '.') }}</span>
            </div>
        </div>

    </div>

    <!-- RAPOR KEGIATAN (Tabel Proyek Berjalan & Margin Real-Time) -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-white tracking-tight">Rapor Kegiatan (Proyek Berjalan)</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Evaluasi komparasi modal keluar vs uang masuk per kegiatan, lengkap dengan indikator profitabilitas.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projections.create') }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 transition-colors">
                    + Simulasi Termin Proyek
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6">Nama Proyek / Klien</th>
                        <th class="py-3.5 px-6 text-right">Nilai Kontrak</th>
                        <th class="py-3.5 px-6 text-right">Pagu Batas Modal</th>
                        <th class="py-3.5 px-6 text-right">Total Uang Masuk</th>
                        <th class="py-3.5 px-6 text-right">Total Penggunaan Dana</th>
                        <th class="py-3.5 px-6 text-right">Margin / Sisa Dana</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($ongoingProjects as $p)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6">
                                <a href="{{ route('projects.show', $p['id']) }}" class="font-bold text-white hover:text-amber-400">
                                    {{ $p['name'] }}
                                </a>
                                <p class="text-slate-400 text-[11px] mt-0.5">Klien: {{ $p['client_name'] }}</p>
                            </td>
                            <td class="py-4 px-6 text-right font-medium text-slate-200">
                                Rp {{ number_format($p['contract_value'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right font-medium text-amber-300">
                                Rp {{ number_format($p['estimated_expense'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-blue-400">
                                Rp {{ number_format($p['total_income'], 0, ',', '.') }}
                                <div class="text-[10px] text-slate-500 font-normal">Cair: Rp {{ number_format($p['realized_income'], 0, ',', '.') }}</div>
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-rose-400">
                                Rp {{ number_format($p['total_expense'], 0, ',', '.') }}
                                <div class="text-[10px] text-slate-500 font-normal">Terpakai: Rp {{ number_format($p['realized_expense'], 0, ',', '.') }}</div>
                            </td>
                            <td class="py-4 px-6 text-right font-bold">
                                <span class="{{ $p['project_margin'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                    Rp {{ number_format($p['project_margin'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($p['is_profitable'])
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        PROFITABEL
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        OVER-BUDGET
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-6 text-center text-slate-500">
                                Belum ada proyek dengan status Ongoing yang berjalan.
                                <a href="{{ route('projections.create') }}" class="text-amber-400 underline font-semibold ml-1">Buat simulasi sekarang &rarr;</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
