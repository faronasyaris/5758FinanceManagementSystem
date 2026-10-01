@extends('layouts.app', ['title' => 'Laporan Keseluruhan'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="no-print flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Laporan Keseluruhan (Report Generator)</h2>
            <p class="text-xs text-slate-400 mt-1">
                Audit trail dan rekapitulasi formal arus kas riil serta proyeksi keuangan untuk kebutuhan manajemen dan pimpinan.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak / Ekspor PDF</span>
            </button>

            <a href="{{ route('reports.export-csv', ['type' => $reportType, 'start_date' => $startDate, 'end_date' => $endDate, 'project_id' => $projectId]) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Unduh Excel / CSV</span>
            </a>
        </div>
    </div>

    <!-- Filter & View Switcher -->
    <div class="no-print p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3">
            <!-- View Selector (Dual Reporting) -->
            <div class="flex items-center rounded-xl bg-slate-800 p-1 border border-slate-700">
                <button type="submit" name="type" value="realisasi"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $reportType === 'realisasi' ? 'bg-amber-400 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white' }}">
                    1. Laporan Realisasi Arus Kas
                </button>
                <button type="submit" name="type" value="proyeksi"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $reportType === 'proyeksi' ? 'bg-amber-400 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white' }}">
                    2. Laporan Proyeksi & Target
                </button>
            </div>

            <!-- Custom Date Range -->
            @if ($reportType === 'realisasi')
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-medium">Dari:</span>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1 text-xs text-white focus:border-amber-400 focus:outline-none">
                    <span class="text-xs text-slate-400 font-medium">s/d:</span>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1 text-xs text-white focus:border-amber-400 focus:outline-none">
                </div>
            @endif

            <!-- Project Filter -->
            <div>
                <select name="project_id" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                    <option value="">Semua Proyek / Kegiatan</option>
                    @foreach ($allProjects as $p)
                        <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-slate-700 hover:bg-slate-600 text-white transition-colors">
                Terapkan Filter
            </button>
        </form>
    </div>

    <!-- PRINT HEADER (Hanya muncul saat dicetak) -->
    <div class="hidden print:block text-slate-950 pb-6 border-b border-slate-300">
        <h1 class="text-2xl font-black tracking-tight">5758inc Finance Management System</h1>
        <p class="text-sm font-semibold uppercase tracking-wider text-slate-600">
            {{ $reportType === 'realisasi' ? 'Laporan Realisasi Arus Kas Perusahaan' : 'Laporan Proyeksi & Pagu Batas Anggaran' }}
        </p>
        <p class="text-xs text-slate-500 mt-1">
            Periode: {{ $reportType === 'realisasi' ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Data Akumulatif per ' . now()->format('d/m/Y') }}
        </p>
    </div>

    <!-- TAMPILAN 1: LAPORAN REALISASI -->
    @if ($reportType === 'realisasi')
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 print-card">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Total Uang Masuk Riil (Cair)</span>
                <div class="text-2xl font-black text-emerald-400 tracking-tight mt-1">
                    Rp {{ number_format($realisasiSummary['total_in'], 0, ',', '.') }}
                </div>
            </div>
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 print-card">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400">Total Pengeluaran Riil</span>
                <div class="text-2xl font-black text-rose-400 tracking-tight mt-1">
                    Rp {{ number_format($realisasiSummary['total_out'], 0, ',', '.') }}
                </div>
            </div>
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 print-card">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Arus Kas Bersih (Net Realized)</span>
                <div class="text-2xl font-black {{ $realisasiSummary['net_cash'] >= 0 ? 'text-amber-400' : 'text-rose-400' }} tracking-tight mt-1">
                    Rp {{ number_format($realisasiSummary['net_cash'], 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Rekapitulasi Per Nama Kegiatan -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden print-card">
            <div class="p-5 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white tracking-tight">Rekapitulasi Kas Berdasarkan Nama Kegiatan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-6">Nama Kegiatan / Proyek</th>
                            <th class="py-3 px-6 text-right">Uang Masuk</th>
                            <th class="py-3 px-6 text-right">Uang Keluar</th>
                            <th class="py-3 px-6 text-right">Surplus / (Defisit)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @forelse ($projectGrouped as $pg)
                            <tr>
                                <td class="py-3.5 px-6 font-bold text-white">{{ $pg['project_name'] }}</td>
                                <td class="py-3.5 px-6 text-right text-emerald-400 font-bold">Rp {{ number_format($pg['total_in'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-6 text-right text-rose-400 font-bold">Rp {{ number_format($pg['total_out'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-6 text-right font-black {{ $pg['net'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                    Rp {{ number_format($pg['net'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 px-6 text-center text-slate-500">Tidak ada transaksi pada rentang tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detail Kronologis Mutasi Transaksi -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden print-card">
            <div class="p-5 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white tracking-tight">Rincian Kronologis Mutasi Kas (Audit Trail)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-6">Tanggal</th>
                            <th class="py-3 px-6">Kegiatan / Proyek</th>
                            <th class="py-3 px-6">Kategori & Keterangan</th>
                            <th class="py-3 px-6 text-center">Jenis</th>
                            <th class="py-3 px-6 text-right">Nominal</th>
                            <th class="py-3 px-6">Sumber Kas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @forelse ($realisasiTransactions as $t)
                            <tr>
                                <td class="py-3.5 px-6 text-slate-300">{{ $t->transaction_date->format('d/m/Y') }}</td>
                                <td class="py-3.5 px-6 font-bold text-white">{{ $t->project ? $t->project->name : '-' }}</td>
                                <td class="py-3.5 px-6">
                                    <span class="font-bold text-slate-200">{{ $t->category ? $t->category->name : '-' }}</span>
                                    <p class="text-[11px] text-slate-400">{{ $t->description ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-6 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $t->type === 'in' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                                        {{ $t->type === 'in' ? 'Uang Masuk' : 'Uang Keluar' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-right font-bold {{ $t->type === 'in' ? 'text-emerald-400' : 'text-rose-400' }}">
                                    Rp {{ number_format($t->nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-6 text-slate-300">{{ $t->account ? $t->account->name : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 px-6 text-center text-slate-500">Tidak ada riwayat transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    <!-- TAMPILAN 2: LAPORAN PROYEKSI -->
    @else
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 print-card">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Nilai Kontrak</span>
                <div class="text-xl font-black text-white tracking-tight mt-1">
                    Rp {{ number_format($projectionSummary['total_contract'], 0, ',', '.') }}
                </div>
            </div>
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 print-card">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Total Target Keuntungan</span>
                <div class="text-xl font-black text-emerald-400 tracking-tight mt-1">
                    Rp {{ number_format($projectionSummary['total_target_margin'], 0, ',', '.') }}
                </div>
            </div>
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 print-card">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Total Pagu Modal (Ceiling)</span>
                <div class="text-xl font-black text-amber-400 tracking-tight mt-1">
                    Rp {{ number_format($projectionSummary['total_budget_ceiling'], 0, ',', '.') }}
                </div>
            </div>
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 print-card">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-400">Total Piutang Berjalan</span>
                <div class="text-xl font-black text-blue-400 tracking-tight mt-1">
                    Rp {{ number_format($projectionSummary['total_pending_receivables'], 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Table of Projections -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden print-card">
            <div class="p-5 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white tracking-tight">Perkiraan Aliran Uang & Target Margin Proyek Masa Depan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-6">Nama Proyek / Klien</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-right">Nilai Kontrak</th>
                            <th class="py-3 px-6 text-right">Target Margin</th>
                            <th class="py-3 px-6 text-right">Pagu Batas Modal</th>
                            <th class="py-3 px-6 text-right">Uang Masuk Riil</th>
                            <th class="py-3 px-6 text-right">Pengeluaran Riil</th>
                            <th class="py-3 px-6 text-right">Sisa Piutang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @forelse ($projectionProjects as $pp)
                            <tr>
                                <td class="py-3.5 px-6">
                                    <span class="font-bold text-white">{{ $pp['name'] }}</span>
                                    <p class="text-[11px] text-slate-400">Klien: {{ $pp['client_name'] }}</p>
                                </td>
                                <td class="py-3.5 px-6 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-300">
                                        {{ $pp['status'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-right text-slate-200">Rp {{ number_format($pp['contract_value'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-6 text-right text-emerald-400 font-bold">Rp {{ number_format($pp['profit_nominal'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-6 text-right text-amber-300 font-bold">Rp {{ number_format($pp['budget_ceiling'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-6 text-right text-blue-400">Rp {{ number_format($pp['realized_income'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-6 text-right text-rose-400">Rp {{ number_format($pp['realized_expense'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-6 text-right font-black text-amber-400">Rp {{ number_format($pp['pending_income'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 px-6 text-center text-slate-500">Tidak ada proyek yang terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
