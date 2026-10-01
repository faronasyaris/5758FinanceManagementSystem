@extends('layouts.app', ['title' => 'Rapor Proyek: ' . $project->name])

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.index') }}" class="text-xs text-slate-400 hover:text-amber-400 transition-colors">&larr; Daftar Proyek</a>
                <span class="text-slate-600">/</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-300">
                    Rapor Keuangan Proyek
                </span>
            </div>
            <h2 class="text-2xl font-bold text-white tracking-tight mt-1">{{ $project->name }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">
                Klien: <strong class="text-slate-200">{{ $project->client ? $project->client->name : '-' }}</strong> |
                Status:
                @if ($project->status === 'ongoing')
                    <span class="text-blue-400 font-bold uppercase">Ongoing</span>
                @elseif ($project->status === 'draft')
                    <span class="text-slate-400 font-bold uppercase">Draft</span>
                @else
                    <span class="text-emerald-400 font-bold uppercase">Completed</span>
                @endif
                | Periode: {{ $project->start_date ? $project->start_date->format('d/m/Y') : '-' }} s/d {{ $project->end_date ? $project->end_date->format('d/m/Y') : 'Selesai' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('transactions.create', ['project_id' => $project->id]) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
                + Catat Transaksi Proyek Ini
            </a>
            <a href="{{ route('projects.edit', $project) }}"
                class="px-3 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                Ubah Info
            </a>
        </div>
    </div>

    <!-- Financial Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Card: Nilai Kontrak -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Nilai Kontrak</span>
            <div class="text-xl font-black text-white tracking-tight mt-2">
                @if ($project->projection)
                    Rp {{ number_format($project->projection->total_contract_value, 0, ',', '.') }}
                @else
                    Rp {{ number_format($project->total_income, 0, ',', '.') }}
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-1">
                @if ($project->projection)
                    Target Margin: {{ $project->projection->target_margin_type === 'percentage' ? $project->projection->target_margin_value . '%' : 'Rp ' . number_format($project->projection->target_margin_value, 0, ',', '.') }}
                @else
                    Belum disimulasikan
                @endif
            </p>
        </div>

        <!-- Card: Pagu Anggaran / Batas Modal -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Pagu Batas Modal (Ceiling)</span>
            <div class="text-xl font-black text-amber-400 tracking-tight mt-2">
                @if ($project->projection)
                    Rp {{ number_format($project->projection->estimated_expense, 0, ',', '.') }}
                @else
                    -
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Batas maksimal pengeluaran modal aman.</p>
        </div>

        <!-- Card: Realisasi Uang Masuk -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-400">Total Uang Masuk</span>
            <div class="text-xl font-black text-blue-400 tracking-tight mt-2">
                Rp {{ number_format($project->total_income, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1 flex justify-between">
                <span>Cair: Rp {{ number_format($project->realized_income, 0, ',', '.') }}</span>
                <span class="text-amber-400 font-semibold">Piutang: Rp {{ number_format($project->pending_income, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Card: Realisasi Pengeluaran & Margin -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-400">Total Pengeluaran</span>
            <div class="text-xl font-black text-rose-400 tracking-tight mt-2">
                Rp {{ number_format($project->total_expense, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1 flex justify-between">
                <span>Terbayar: Rp {{ number_format($project->realized_expense, 0, ',', '.') }}</span>
                <span class="text-rose-400 font-semibold">Hutang: Rp {{ number_format($project->pending_expense, 0, ',', '.') }}</span>
            </div>
        </div>

    </div>

    <!-- Margin Alert Box -->
    <div class="p-5 rounded-2xl {{ $project->isProfitable() ? 'bg-emerald-500/10 border border-emerald-500/30' : 'bg-rose-500/10 border border-rose-500/30' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full {{ $project->isProfitable() ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                <span class="text-xs font-bold uppercase tracking-wider {{ $project->isProfitable() ? 'text-emerald-300' : 'text-rose-300' }}">
                    Status Margin Finansial: {{ $project->isProfitable() ? 'PROFITABEL (SURPLUS)' : 'OVER-BUDGET (DEFISIT)' }}
                </span>
            </div>
            <p class="text-xs text-slate-300 mt-1">
                Margin Dihitung dari: (Total Masuk Cair + Piutang) - (Total Keluar Terbayar + Kasbon/Hutang).
            </p>
        </div>
        <div class="text-right">
            <span class="text-[11px] text-slate-400 uppercase font-semibold">Margin / Sisa Dana:</span>
            <div class="text-2xl font-black {{ $project->isProfitable() ? 'text-emerald-400' : 'text-rose-400' }}">
                Rp {{ number_format($project->project_margin, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Transaksi Proyek -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white tracking-tight">Histori Mutasi & Jadwal Termin Proyek</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar arus kas riil dan piutang termin yang terkait langsung dengan kegiatan ini.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6">Kategori & Keterangan</th>
                        <th class="py-3.5 px-6 text-center">Jenis Aliran</th>
                        <th class="py-3.5 px-6 text-right">Nominal</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6">Sumber Kas</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($transactions as $tx)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6 text-slate-300 whitespace-nowrap">
                                {{ $tx->transaction_date->format('d/m/Y') }}
                                @if ($tx->due_date && $tx->payment_status === 'pending')
                                    <div class="text-[10px] {{ $tx->isOverdue() ? 'text-rose-400 font-bold' : 'text-slate-400' }}">
                                        Tempo: {{ $tx->due_date->format('d/m/Y') }}
                                        @if ($tx->isOverdue())
                                            (Overdue)
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-white">{{ $tx->category ? $tx->category->name : '-' }}</span>
                                <p class="text-slate-400 text-[11px] mt-0.5">{{ $tx->description ?? '-' }}</p>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($tx->type === 'in')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Uang Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Uang Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-bold {{ $tx->type === 'in' ? 'text-emerald-400' : 'text-rose-400' }}">
                                Rp {{ number_format($tx->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($tx->payment_status === 'paid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Lunas / Cair
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/10 text-amber-300 border border-amber-400/20">
                                        Belum Cair (Pending)
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-300">
                                {{ $tx->account ? $tx->account->name : '-' }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                @if ($tx->payment_status === 'pending')
                                    <button onclick="openMarkPaidModal('{{ $tx->id }}', '{{ $tx->description ?? $tx->category->name }}', '{{ number_format($tx->nominal, 0, ',', '.') }}', '{{ $tx->type }}')"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-sm transition-colors">
                                        Tandai Lunas
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-6 text-center text-slate-500">
                                Belum ada mutasi transaksi untuk proyek ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Pelunasan (Tandai Lunas) -->
<div id="markPaidModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white tracking-tight">Konfirmasi Pelunasan / Pencairan</h3>
            <button onclick="closeMarkPaidModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form id="markPaidForm" method="POST" action="" class="space-y-4">
            @csrf

            <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/60 text-xs text-slate-300 space-y-1">
                <div>Keterangan: <strong id="modalTxDesc" class="text-white"></strong></div>
                <div>Nominal: <strong id="modalTxNominal" class="text-amber-400 text-sm"></strong></div>
            </div>

            <div>
                <label for="modalAccountId" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Pilih Rekening Tujuan / Asal Kas</label>
                <select id="modalAccountId" name="account_id" required
                    class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none">
                    @foreach (\App\Models\Account::orderBy('name')->get() as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="settlement_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Tanggal Transaksi Riil</label>
                <input type="date" id="settlement_date" name="settlement_date" required value="{{ now()->toDateString() }}"
                    class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeMarkPaidModal()" class="px-3 py-1.5 rounded-xl text-xs text-slate-400 hover:text-white">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md">
                    Proses Pelunasan & Update Kas
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openMarkPaidModal(id, desc, nominal, type) {
        const form = document.getElementById('markPaidForm');
        form.action = `/transactions/${id}/mark-as-paid`;
        document.getElementById('modalTxDesc').innerText = desc;
        document.getElementById('modalTxNominal').innerText = 'Rp ' + nominal;
        document.getElementById('markPaidModal').classList.remove('hidden');
    }

    function closeMarkPaidModal() {
        document.getElementById('markPaidModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
