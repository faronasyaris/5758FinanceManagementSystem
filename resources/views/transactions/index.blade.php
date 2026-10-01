@extends('layouts.app', ['title' => 'Pencatatan Arus Kas'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Pencatatan Arus Kas & Manajemen Termin</h2>
            <p class="text-xs text-slate-400 mt-1">
                Catat seluruh uang masuk, pengeluaran operasional, kasbon tim, dan kelola pelunasan termin bertahap.
            </p>
        </div>
        <a href="{{ route('transactions.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Catat Transaksi Baru</span>
        </a>
    </div>

    <!-- Summary Strip -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Uang Masuk Cair (Lunas)</span>
            <div class="text-lg font-black text-emerald-400 mt-1">Rp {{ number_format($totalPaidIncome, 0, ',', '.') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400">Uang Keluar Riil (Lunas)</span>
            <div class="text-lg font-black text-rose-400 mt-1">Rp {{ number_format($totalPaidExpense, 0, ',', '.') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-400">Total Piutang (Pending In)</span>
            <div class="text-lg font-black text-blue-400 mt-1">Rp {{ number_format($totalPendingIncome, 0, ',', '.') }}</div>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Total Kasbon / Kewajiban (Pending Out)</span>
            <div class="text-lg font-black text-amber-400 mt-1">Rp {{ number_format($totalPendingExpense, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('transactions.index') }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex flex-wrap items-center gap-3">
        <!-- Jenis Transaksi -->
        <div>
            <select name="type" onchange="this.form.submit()"
                class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                <option value="">Semua Aliran Dana</option>
                <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Uang Masuk (Income)</option>
                <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Uang Keluar (Expense)</option>
            </select>
        </div>

        <!-- Status Pembayaran -->
        <div>
            <select name="status" onchange="this.form.submit()"
                class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas / Cair</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Belum Cair (Pending)</option>
            </select>
        </div>

        <!-- Filter Proyek -->
        <div>
            <select name="project_id" onchange="this.form.submit()"
                class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none max-w-[180px]">
                <option value="">Semua Proyek</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                        {{ $proj->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filter Rekening -->
        <div>
            <select name="account_id" onchange="this.form.submit()"
                class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                <option value="">Semua Rekening Kas</option>
                @foreach ($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                        {{ $acc->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Overdue Checkbox -->
        <label class="flex items-center gap-1.5 text-xs text-rose-400 font-semibold cursor-pointer pl-1">
            <input type="checkbox" name="overdue" value="1" onchange="this.form.submit()" {{ request('overdue') ? 'checked' : '' }}
                class="rounded border-slate-700 bg-slate-800 text-rose-500 focus:ring-rose-400">
            <span>Hanya Yang Jatuh Tempo (Overdue)</span>
        </label>

        @if (request()->hasAny(['type', 'status', 'project_id', 'account_id', 'overdue']))
            <a href="{{ route('transactions.index') }}" class="text-xs text-slate-400 hover:text-white underline ml-auto">
                Reset Filter
            </a>
        @endif
    </form>

    <!-- Table of Transactions -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase font-semibold text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6">Nama Proyek</th>
                        <th class="py-3.5 px-6">Kategori & Keterangan</th>
                        <th class="py-3.5 px-6 text-center">Jenis</th>
                        <th class="py-3.5 px-6 text-right">Nominal</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6">Sumber Kas</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($transactions as $tx)
                        <tr class="hover:bg-slate-800/40 transition-colors {{ $tx->isOverdue() ? 'bg-rose-500/5' : '' }}">
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
                                <a href="{{ route('projects.show', $tx->project_id) }}" class="font-bold text-white hover:text-amber-400">
                                    {{ $tx->project ? $tx->project->name : '-' }}
                                </a>
                                <p class="text-slate-400 text-[10px]">{{ $tx->project && $tx->project->client ? $tx->project->client->name : '-' }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-200">{{ $tx->category ? $tx->category->name : '-' }}</span>
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
                            <td class="py-4 px-6 text-right font-black text-sm {{ $tx->type === 'in' ? 'text-emerald-400' : 'text-rose-400' }}">
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
                            <td class="py-4 px-6 text-right space-x-2">
                                @if ($tx->payment_status === 'pending')
                                    <button onclick="openMarkPaidModal('{{ $tx->id }}', '{{ $tx->description ?? $tx->category->name }}', '{{ number_format($tx->nominal, 0, ',', '.') }}', '{{ $tx->type }}')"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-sm transition-colors">
                                        Tandai Lunas
                                    </button>
                                @endif
                                <form method="POST" action="{{ route('transactions.destroy', $tx) }}" class="inline-block" onsubmit="return confirm('Hapus transaksi ini? Jika transaksi lunas, saldo akun terkait akan disesuaikan kembali.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 rounded-lg text-[11px] font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 px-6 text-center text-slate-500">
                                Tidak ada transaksi yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $transactions->links() }}
            </div>
        @endif
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
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Saldo rekening yang dipilih akan otomatis disesuaikan secara real-time.</p>
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
