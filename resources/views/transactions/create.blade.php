@extends('layouts.app', ['title' => 'Catat Arus Kas'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="pb-4 border-b border-slate-800">
        <a href="{{ route('transactions.index') }}" class="text-xs text-slate-400 hover:text-amber-400 transition-colors">&larr; Kembali ke Buku Kas</a>
        <h2 class="text-xl font-bold text-white tracking-tight mt-2">Catat Mutasi Arus Kas Baru</h2>
        <p class="text-xs text-slate-400 mt-1">Form input bebas jargon akuntansi untuk uang masuk maupun uang keluar operasional.</p>
    </div>

    <form method="POST" action="{{ route('transactions.store') }}" class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl space-y-5">
        @csrf

        <!-- Jenis Mutasi (In / Out) -->
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Jenis Aliran Dana</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-500/10 transition-all">
                    <input type="radio" name="type" value="in" id="typeIn" {{ old('type', request('type')) === 'out' ? '' : 'checked' }}
                        onchange="filterCategories()"
                        class="text-emerald-500 focus:ring-emerald-400">
                    <div>
                        <div class="text-xs font-bold text-emerald-400">Uang Masuk</div>
                        <div class="text-[10px] text-slate-400">Termin Klien, DP, Pendapatan</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer has-[:checked]:border-rose-400 has-[:checked]:bg-rose-500/10 transition-all">
                    <input type="radio" name="type" value="out" id="typeOut" {{ old('type', request('type')) === 'out' ? 'checked' : '' }}
                        onchange="filterCategories()"
                        class="text-rose-500 focus:ring-rose-400">
                    <div>
                        <div class="text-xs font-bold text-rose-400">Uang Keluar</div>
                        <div class="text-[10px] text-slate-400">Honor, Operasional, Material, Kasbon</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Tagging Proyek / Kegiatan -->
        <div>
            <label for="project_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Tagging Proyek / Kegiatan</label>
            <select id="project_id" name="project_id" required
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none">
                <option value="">-- Pilih Proyek Terkait --</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}" {{ old('project_id', request('project_id')) == $proj->id ? 'selected' : '' }}>
                        {{ $proj->name }} ({{ $proj->client ? $proj->client->name : '-' }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Kategori Transaksi -->
        <div>
            <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Kategori Transaksi</label>
            <select id="category_id" name="category_id" required
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none">
                <option value="">-- Pilih Kategori --</option>
                <!-- Populated dynamically via JS -->
            </select>
        </div>

        <!-- Nominal Transaksi -->
        <div>
            <label for="nominal" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Nominal Transaksi (Rp)</label>
            <input type="number" step="0.01" min="1" id="nominal" name="nominal" required value="{{ old('nominal') }}"
                placeholder="0"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-base font-bold text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="transaction_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Tanggal Pencatatan</label>
                <input type="date" id="transaction_date" name="transaction_date" required value="{{ old('transaction_date', now()->toDateString()) }}"
                    class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none">
            </div>

            <!-- Status Pembayaran -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Status Pembayaran</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-500/10">
                        <input type="radio" name="payment_status" value="paid" id="statusPaid" checked onchange="toggleStatusFields()"
                            class="text-emerald-500 focus:ring-emerald-400">
                        <span class="text-xs font-bold text-slate-200">Lunas / Cair</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer has-[:checked]:border-amber-400 has-[:checked]:bg-amber-400/10">
                        <input type="radio" name="payment_status" value="pending" id="statusPending" onchange="toggleStatusFields()"
                            class="text-amber-400 focus:ring-amber-400">
                        <span class="text-xs font-bold text-slate-200">Belum Cair (Pending)</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Rekening Kas (Wajib jika status Lunas) -->
        <div id="accountField">
            <label for="account_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                Pilih Sumber Kas (Rekening Bank / Kas Tunai)
            </label>
            <select id="account_id" name="account_id"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none">
                <option value="">-- Pilih Rekening Kas --</option>
                @foreach ($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                        {{ $acc->name }} (Saldo Saat Ini: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
            <p class="text-[11px] text-slate-400 mt-1">Saldo rekening yang dipilih akan otomatis bertambah atau berkurang.</p>
        </div>

        <!-- Due Date Field (Muncul jika status Pending) -->
        <div id="dueDateField" class="hidden">
            <label for="due_date" class="block text-xs font-semibold uppercase tracking-wider text-rose-400">
                Tanggal Jatuh Tempo (Overdue Radar Target)
            </label>
            <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}"
                class="mt-1.5 block w-full rounded-xl border border-rose-500/40 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-rose-400 focus:outline-none">
            <p class="text-[11px] text-slate-400 mt-1">Jika tanggal ini terlewati dan belum lunas, sistem akan otomatis membunyikan radar peringatan merah di Dasbor Eksekutif.</p>
        </div>

        <!-- Deskripsi / Keterangan -->
        <div>
            <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Keterangan Tambahan / Deskripsi</label>
            <textarea id="description" name="description" rows="2"
                placeholder="Contoh: Pembayaran Termin 1 DP 30%, Kasbon transport survei lapangan tim A"
                class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none">{{ old('description') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="{{ route('transactions.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                Batal
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all">
                Simpan Transaksi
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const incomeCategories = @json($incomeCategories);
    const expenseCategories = @json($expenseCategories);
    const categorySelect = document.getElementById('category_id');
    const oldCategoryId = "{{ old('category_id') }}";

    function filterCategories() {
        const isIncome = document.getElementById('typeIn').checked;
        const list = isIncome ? incomeCategories : expenseCategories;

        categorySelect.innerHTML = '<option value="">-- Pilih Kategori --</option>';
        list.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.innerText = c.name;
            if (oldCategoryId && oldCategoryId == c.id) {
                opt.selected = true;
            }
            categorySelect.appendChild(opt);
        });
    }

    function toggleStatusFields() {
        const isPending = document.getElementById('statusPending').checked;
        const accountField = document.getElementById('accountField');
        const dueDateField = document.getElementById('dueDateField');
        const accountInput = document.getElementById('account_id');

        if (isPending) {
            dueDateField.classList.remove('hidden');
            accountInput.removeAttribute('required');
        } else {
            dueDateField.classList.add('hidden');
            accountInput.setAttribute('required', 'required');
        }
    }

    // Initial run
    filterCategories();
    toggleStatusFields();
</script>
@endpush
@endsection
