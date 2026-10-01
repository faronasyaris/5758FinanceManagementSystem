@extends('layouts.app', ['title' => 'Simulasi & Kalkulator Proyek'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="pb-4 border-b border-slate-800">
        <a href="{{ route('projections.index') }}" class="text-xs text-slate-400 hover:text-amber-400 transition-colors">&larr; Kembali ke Proyeksi</a>
        <h2 class="text-xl font-bold text-white tracking-tight mt-2">Kalkulator Proyeksi & Auto-Termin Generator</h2>
        <p class="text-xs text-slate-400 mt-1">
            Simulasikan nilai kontrak, tetapkan target profit, dan rancang jadwal termin penagihan otomatis sebelum proyek dieksekusi.
        </p>
    </div>

    <form method="POST" action="{{ route('projections.store') }}" id="projectionForm" class="space-y-6">
        @csrf

        <!-- Bagian 1: Data Pokok Kegiatan -->
        <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl space-y-5">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-amber-400">1. Identitas Proyek / Kegiatan</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="client_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Pilih Klien / Instansi</label>
                    <select id="client_id" name="client_id" required
                        class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none">
                        <option value="">-- Pilih Klien --</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="project_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Nama Proyek / Kegiatan</label>
                    <input type="text" id="project_name" name="project_name" required value="{{ old('project_name') }}"
                        placeholder="Contoh: Pengadaan Server Utama & Jaringan"
                        class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none">
                </div>

                <div>
                    <label for="start_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Tanggal Mulai Proyek</label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}"
                        class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none">
                </div>

                <div>
                    <label for="end_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Estimasi Selesai</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                        class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white focus:border-amber-400 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Bagian 2: Smart Margin Calculation -->
        <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl space-y-5">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-amber-400">2. Smart Margin Calculation (Kalkulasi Pagu)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="total_contract_value" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Total Nilai Kontrak (Rp)</label>
                    <input type="number" step="0.01" min="1" id="total_contract_value" name="total_contract_value" required value="{{ old('total_contract_value', 100000000) }}"
                        class="mt-1.5 block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-base font-bold text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Nilai bruto kontrak yang disepakati dengan klien.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Model Target Keuntungan</label>
                    <div class="grid grid-cols-2 gap-3 mb-2">
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer has-[:checked]:border-amber-400 has-[:checked]:bg-amber-400/10">
                            <input type="radio" name="target_margin_type" value="percentage" checked id="typePercentage" class="text-amber-400 focus:ring-amber-400">
                            <span class="text-xs font-bold text-slate-200">Persentase (%)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-700 bg-slate-800/60 cursor-pointer has-[:checked]:border-amber-400 has-[:checked]:bg-amber-400/10">
                            <input type="radio" name="target_margin_type" value="nominal" id="typeNominal" class="text-amber-400 focus:ring-amber-400">
                            <span class="text-xs font-bold text-slate-200">Nominal (Rp)</span>
                        </label>
                    </div>

                    <input type="number" step="0.01" min="0" id="target_margin_value" name="target_margin_value" required value="{{ old('target_margin_value', 15) }}"
                        class="block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm font-bold text-white focus:border-amber-400 focus:outline-none">
                </div>
            </div>

            <!-- Smart Output Box -->
            <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-800 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Target Profit Perusahaan:</span>
                    <div id="displayProfitNominal" class="text-xl font-black text-emerald-400 mt-1">Rp 15.000.000</div>
                    <span id="displayProfitPct" class="text-[10px] text-emerald-300 font-semibold">(15.0% dari nilai kontrak)</span>
                </div>

                <div class="p-3 rounded-lg bg-amber-400/10 border border-amber-400/20">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Pagu Batas Modal (Strict Ceiling):</span>
                    <div id="displayEstimatedExpense" class="text-xl font-black text-amber-400 mt-1">Rp 85.000.000</div>
                    <span class="text-[10px] text-amber-300 font-semibold">Batas pengeluaran maksimal agar profit tercapai</span>
                </div>
            </div>
        </div>

        <!-- Bagian 3: Auto-Termin Generator -->
        <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider text-amber-400">3. Auto-Termin Generator (Pemecah Tagihan)</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Tentukan skema pembayaran klien secara sekaligus atau termin bertahap.</p>
                </div>
                <div class="flex items-center gap-3">
                    <select id="paymentStructure" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                        <option value="sekaligus">Bayar Sekaligus (1x 100%)</option>
                        <option value="bertahap" selected>Bayar Bertahap (Termin)</option>
                    </select>
                    <select id="termCountSelect" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                        <option value="2">2 Termin</option>
                        <option value="3" selected>3 Termin</option>
                        <option value="4">4 Termin</option>
                        <option value="5">5 Termin</option>
                    </select>
                </div>
            </div>

            <!-- Dynamic Terms Rows Container -->
            <div id="termsContainer" class="space-y-3">
                <!-- Dynamically populated via JS -->
            </div>

            <div class="p-3 rounded-xl bg-slate-800/40 border border-slate-700/60 flex items-center justify-between text-xs">
                <span class="text-slate-400">Total Persentase Termin:</span>
                <span id="termsTotalPct" class="font-bold text-emerald-400 text-sm">100%</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-3">
            <input type="hidden" name="action" id="formAction" value="save_draft">

            <button type="button" onclick="submitForm('save_draft')"
                class="w-full sm:w-auto px-5 py-3 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all">
                Simpan Sebagai Simulasi (Draft)
            </button>

            <button type="button" onclick="submitForm('execute_now')"
                class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs font-black bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 shadow-xl shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span>Jalankan Proyek (Ongoing & Terbitkan Piutang)</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const contractInput = document.getElementById('total_contract_value');
    const marginTypeInputs = document.querySelectorAll('input[name="target_margin_type"]');
    const marginValueInput = document.getElementById('target_margin_value');
    const profitNominalDisplay = document.getElementById('displayProfitNominal');
    const profitPctDisplay = document.getElementById('displayProfitPct');
    const estimatedExpenseDisplay = document.getElementById('displayEstimatedExpense');
    const paymentStructure = document.getElementById('paymentStructure');
    const termCountSelect = document.getElementById('termCountSelect');
    const termsContainer = document.getElementById('termsContainer');
    const termsTotalPct = document.getElementById('termsTotalPct');

    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function calculateMargin() {
        const contract = parseFloat(contractInput.value) || 0;
        const isPct = document.getElementById('typePercentage').checked;
        const val = parseFloat(marginValueInput.value) || 0;

        let profitNominal = 0;
        let profitPct = 0;

        if (isPct) {
            profitPct = val;
            profitNominal = contract * (val / 100);
        } else {
            profitNominal = val;
            profitPct = contract > 0 ? (val / contract) * 100 : 0;
        }

        const ceiling = Math.max(0, contract - profitNominal);

        profitNominalDisplay.innerText = formatRupiah(profitNominal);
        profitPctDisplay.innerText = `(${profitPct.toFixed(1)}% dari nilai kontrak)`;
        estimatedExpenseDisplay.innerText = formatRupiah(ceiling);

        // Update termin amounts
        updateTermsNominals();
    }

    function buildTerms() {
        const mode = paymentStructure.value;
        const contract = parseFloat(contractInput.value) || 0;
        termsContainer.innerHTML = '';

        if (mode === 'sekaligus') {
            termCountSelect.style.display = 'none';
            renderTermRow(0, 'Pembayaran Penuh (100%)', 100, 30);
        } else {
            termCountSelect.style.display = 'inline-block';
            const count = parseInt(termCountSelect.value);

            let presets = [];
            if (count === 2) {
                presets = [{ name: 'Uang Muka (DP)', pct: 50, days: 7 }, { name: 'Pelunasan', pct: 50, days: 30 }];
            } else if (count === 3) {
                presets = [{ name: 'Uang Muka (DP)', pct: 30, days: 7 }, { name: 'Termin 1 (Progress)', pct: 40, days: 30 }, { name: 'Pelunasan Akhir', pct: 30, days: 60 }];
            } else if (count === 4) {
                presets = [{ name: 'Uang Muka (DP)', pct: 25, days: 7 }, { name: 'Termin 1', pct: 25, days: 20 }, { name: 'Termin 2', pct: 25, days: 40 }, { name: 'Pelunasan', pct: 25, days: 60 }];
            } else if (count === 5) {
                presets = [{ name: 'Uang Muka (DP)', pct: 20, days: 7 }, { name: 'Termin 1', pct: 20, days: 20 }, { name: 'Termin 2', pct: 20, days: 40 }, { name: 'Termin 3', pct: 20, days: 60 }, { name: 'Pelunasan', pct: 20, days: 80 }];
            }

            presets.forEach((p, idx) => {
                renderTermRow(idx, p.name, p.pct, p.days);
            });
        }

        updateTermsNominals();
    }

    function renderTermRow(idx, defaultName, defaultPct, addDays) {
        const today = new Date();
        const dueDate = new Date(today);
        dueDate.setDate(dueDate.getDate() + addDays);
        const dateStr = dueDate.toISOString().split('T')[0];

        const row = document.createElement('div');
        row.className = 'p-3.5 rounded-xl bg-slate-800/70 border border-slate-700/70 grid grid-cols-1 md:grid-cols-4 gap-3 items-center';
        row.innerHTML = `
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400">Tahap Termin</label>
                <input type="text" name="terms[${idx}][name]" value="${defaultName}" required
                    class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400">Persentase (%)</label>
                <input type="number" step="0.1" min="1" max="100" name="terms[${idx}][percentage]" value="${defaultPct}" required
                    oninput="updateTermsNominals()"
                    class="term-pct mt-1 block w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400">Nominal Tagihan (Rp)</label>
                <input type="text" readonly name="terms[${idx}][nominal_display]" id="termNominalDisplay_${idx}"
                    class="mt-1 block w-full rounded-lg border border-slate-700/60 bg-slate-950 px-3 py-1.5 text-xs font-bold text-amber-400">
                <input type="hidden" name="terms[${idx}][nominal]" id="termNominalVal_${idx}">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400">Target Jatuh Tempo</label>
                <input type="date" name="terms[${idx}][due_date]" value="${dateStr}" required
                    class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white">
            </div>
        `;
        termsContainer.appendChild(row);
    }

    function updateTermsNominals() {
        const contract = parseFloat(contractInput.value) || 0;
        const pctInputs = document.querySelectorAll('.term-pct');
        let totalPct = 0;

        pctInputs.forEach((input, i) => {
            const pct = parseFloat(input.value) || 0;
            totalPct += pct;
            const nominal = contract * (pct / 100);

            const displayEl = document.getElementById(`termNominalDisplay_${i}`);
            const valEl = document.getElementById(`termNominalVal_${i}`);
            if (displayEl && valEl) {
                displayEl.value = formatRupiah(nominal);
                valEl.value = Math.round(nominal);
            }
        });

        termsTotalPct.innerText = totalPct.toFixed(1) + '%';
        if (Math.round(totalPct) === 100) {
            termsTotalPct.className = 'font-bold text-emerald-400 text-sm';
        } else {
            termsTotalPct.className = 'font-bold text-rose-400 text-sm';
        }
    }

    function submitForm(actionValue) {
        document.getElementById('formAction').value = actionValue;
        document.getElementById('projectionForm').submit();
    }

    contractInput.addEventListener('input', calculateMargin);
    marginValueInput.addEventListener('input', calculateMargin);
    marginTypeInputs.forEach(r => r.addEventListener('change', calculateMargin));
    paymentStructure.addEventListener('change', buildTerms);
    termCountSelect.addEventListener('change', buildTerms);

    // Initial load
    calculateMargin();
    buildTerms();
</script>
@endpush
@endsection
