@extends('layouts.app', ['title' => 'Simulasi & Kalkulator Proyek'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="pb-4 border-b border-slate-800">
        <a href="{{ route('projections.index') }}" class="text-xs text-slate-400 hover:text-amber-400 transition-colors">&larr; Kembali ke Proyeksi</a>
        <h2 class="text-xl font-bold text-white tracking-tight mt-2">Kalkulator Proyeksi & Auto-Termin Generator</h2>
        <p class="text-xs text-slate-400 mt-1">
            Simulasikan nilai kontrak, tetapkan target profit, dan rancang jadwal termin pembayaran fleksibel (situasional bebas sesuai kesepakatan klien).
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

        <!-- Bagian 3: Auto-Termin Generator (Situasional & Fleksibel) -->
        <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-800">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider text-amber-400">3. Jadwal Termin Pembayaran (Situasional)</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            Bebas Atur Jumlah Termin
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Admin bebas menambah baris termin sebanyak apa pun sesuai kesepakatan kontrak.
                    </p>
                </div>

                <!-- Template & Toolbar Controls -->
                <div class="flex flex-wrap items-center gap-2">
                    <select id="paymentStructure" onchange="handleStructureChange()" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                        <option value="bertahap" selected>Struktur: Bertahap (Termin)</option>
                        <option value="sekaligus">Struktur: Sekaligus (1x 100%)</option>
                    </select>

                    <select id="termPresetSelect" onchange="applyPreset(this.value)" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                        <option value="">-- Preset Cepat (Opsional) --</option>
                        <option value="2">2 Tahap (50% - 50%)</option>
                        <option value="3" selected>3 Tahap (30% - 40% - 30%)</option>
                        <option value="4">4 Tahap (25% x 4)</option>
                        <option value="5">5 Tahap (20% x 5)</option>
                        <option value="6">6 Tahap (Termin Berkala)</option>
                        <option value="8">8 Tahap (Proyek Panjang)</option>
                        <option value="10">10 Tahap (Cicilan Termin)</option>
                        <option value="12">12 Tahap (Termin Bulanan 1 Thn)</option>
                    </select>

                    <button type="button" onclick="distributeEvenly()"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors"
                        title="Bagi rata 100% ke seluruh baris termin yang ada">
                        ⚡ Bagi Rata
                    </button>

                    <button type="button" onclick="addCustomTermRow()"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md shadow-amber-400/20 transition-all flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>+ Tambah Baris Termin</span>
                    </button>
                </div>
            </div>

            <!-- Dynamic Terms Rows Container -->
            <div id="termsContainer" class="space-y-3">
                <!-- Dynamically populated via JS -->
            </div>

            <!-- Total Calculation & Status Validation Strip -->
            <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="text-slate-400">Total Alokasi Termin:</span>
                    <span id="termsTotalPct" class="font-black text-sm px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        100%
                    </span>
                    <span id="termsTotalNominal" class="font-bold text-slate-200">
                        Rp 0
                    </span>
                </div>

                <div id="termsValidationMsg" class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span>Alokasi termin tepat 100% dari nilai kontrak.</span>
                </div>
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
    const termsContainer = document.getElementById('termsContainer');
    const termsTotalPct = document.getElementById('termsTotalPct');
    const termsTotalNominal = document.getElementById('termsTotalNominal');
    const termsValidationMsg = document.getElementById('termsValidationMsg');

    // Array holding all current term items
    let termItems = [];

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

        // Recalculate nominals across all active terms
        syncAllTermsFromPercentages();
    }

    function handleStructureChange() {
        if (paymentStructure.value === 'sekaligus') {
            termItems = [{
                name: 'Pembayaran Penuh (100%)',
                percentage: 100,
                nominal: parseFloat(contractInput.value) || 0,
                due_date: getDefaultDueDate(30)
            }];
            renderAllTerms();
        } else {
            applyPreset("3");
        }
    }

    function getDefaultDueDate(daysFromNow) {
        const d = new Date();
        d.setDate(d.getDate() + daysFromNow);
        return d.toISOString().split('T')[0];
    }

    function applyPreset(countStr) {
        if (!countStr) return;
        const count = parseInt(countStr);
        const contract = parseFloat(contractInput.value) || 0;
        termItems = [];

        if (count === 2) {
            termItems = [
                { name: 'Uang Muka (DP)', percentage: 50, nominal: contract * 0.5, due_date: getDefaultDueDate(7) },
                { name: 'Pelunasan Akhir', percentage: 50, nominal: contract * 0.5, due_date: getDefaultDueDate(30) }
            ];
        } else if (count === 3) {
            termItems = [
                { name: 'Uang Muka (DP)', percentage: 30, nominal: contract * 0.3, due_date: getDefaultDueDate(7) },
                { name: 'Termin 1 (Progress Fisik)', percentage: 40, nominal: contract * 0.4, due_date: getDefaultDueDate(30) },
                { name: 'Pelunasan Akhir', percentage: 30, nominal: contract * 0.3, due_date: getDefaultDueDate(60) }
            ];
        } else {
            const evenPct = parseFloat((100 / count).toFixed(2));
            for (let i = 1; i <= count; i++) {
                let pName = i === 1 ? 'Uang Muka (DP)' : (i === count ? 'Pelunasan Akhir' : `Termin ${i - 1}`);
                let pct = (i === count) ? parseFloat((100 - (evenPct * (count - 1))).toFixed(2)) : evenPct;
                termItems.push({
                    name: pName,
                    percentage: pct,
                    nominal: contract * (pct / 100),
                    due_date: getDefaultDueDate(i * 15)
                });
            }
        }

        renderAllTerms();
    }

    function addCustomTermRow() {
        const contract = parseFloat(contractInput.value) || 0;
        const currentCount = termItems.length;
        const nextIndex = currentCount + 1;

        // Auto calculate remaining percentage
        let usedPct = 0;
        termItems.forEach(t => usedPct += (parseFloat(t.percentage) || 0));
        let remainingPct = Math.max(0, parseFloat((100 - usedPct).toFixed(2)));

        termItems.push({
            name: `Termin ${nextIndex}`,
            percentage: remainingPct > 0 ? remainingPct : 10,
            nominal: contract * ((remainingPct > 0 ? remainingPct : 10) / 100),
            due_date: getDefaultDueDate(nextIndex * 20)
        });

        renderAllTerms();
    }

    function removeTermRow(index) {
        if (termItems.length <= 1) {
            alert('Minimal harus ada 1 termin pembayaran.');
            return;
        }
        termItems.splice(index, 1);
        renderAllTerms();
    }

    function distributeEvenly() {
        if (termItems.length === 0) return;
        const contract = parseFloat(contractInput.value) || 0;
        const count = termItems.length;
        const evenPct = parseFloat((100 / count).toFixed(2));

        termItems.forEach((term, i) => {
            term.percentage = (i === count - 1) ? parseFloat((100 - (evenPct * (count - 1))).toFixed(2)) : evenPct;
            term.nominal = contract * (term.percentage / 100);
        });

        renderAllTerms();
    }

    function onTermPercentageChange(index, newPct) {
        const contract = parseFloat(contractInput.value) || 0;
        const pct = parseFloat(newPct) || 0;
        termItems[index].percentage = pct;
        termItems[index].nominal = contract * (pct / 100);

        // Update display input without full rerender to preserve focus
        const nominalDisplay = document.getElementById(`termNominalDisplay_${index}`);
        const nominalHidden = document.getElementById(`termNominalVal_${index}`);
        if (nominalDisplay) nominalDisplay.value = formatRupiah(termItems[index].nominal);
        if (nominalHidden) nominalHidden.value = Math.round(termItems[index].nominal);

        updateTotalSummary();
    }

    function onTermNominalChange(index, newNominal) {
        const contract = parseFloat(contractInput.value) || 0;
        const nom = parseFloat(newNominal) || 0;
        termItems[index].nominal = nom;
        const pct = contract > 0 ? parseFloat(((nom / contract) * 100).toFixed(2)) : 0;
        termItems[index].percentage = pct;

        const pctInput = document.getElementById(`termPctInput_${index}`);
        const nominalDisplay = document.getElementById(`termNominalDisplay_${index}`);
        const nominalHidden = document.getElementById(`termNominalVal_${index}`);

        if (pctInput) pctInput.value = pct;
        if (nominalHidden) nominalHidden.value = Math.round(nom);

        updateTotalSummary();
    }

    function syncAllTermsFromPercentages() {
        const contract = parseFloat(contractInput.value) || 0;
        termItems.forEach((t, i) => {
            t.nominal = contract * ((parseFloat(t.percentage) || 0) / 100);
            const nominalDisplay = document.getElementById(`termNominalDisplay_${i}`);
            const nominalHidden = document.getElementById(`termNominalVal_${i}`);
            if (nominalDisplay) nominalDisplay.value = formatRupiah(t.nominal);
            if (nominalHidden) nominalHidden.value = Math.round(t.nominal);
        });
        updateTotalSummary();
    }

    function renderAllTerms() {
        termsContainer.innerHTML = '';
        const contract = parseFloat(contractInput.value) || 0;

        termItems.forEach((term, idx) => {
            const row = document.createElement('div');
            row.className = 'p-3.5 rounded-xl bg-slate-800/70 border border-slate-700/70 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center';
            row.id = `termRow_${idx}`;
            row.innerHTML = `
                <div class="sm:col-span-4">
                    <label class="block text-[10px] font-bold uppercase text-slate-400">Nama Tahap Termin #${idx + 1}</label>
                    <input type="text" name="terms[${idx}][name]" value="${term.name}" required
                        onchange="termItems[${idx}].name = this.value"
                        placeholder="Contoh: DP 30%, Termin Struktur 40%"
                        class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-bold uppercase text-slate-400">Porsi (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="terms[${idx}][percentage]" value="${term.percentage}" required
                        id="termPctInput_${idx}"
                        oninput="onTermPercentageChange(${idx}, this.value)"
                        class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs font-bold text-white focus:border-amber-400 focus:outline-none">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-[10px] font-bold uppercase text-slate-400">Nominal Tagihan (Rp)</label>
                    <input type="text" readonly name="terms[${idx}][nominal_display]" id="termNominalDisplay_${idx}" value="${formatRupiah(term.nominal)}"
                        class="mt-1 block w-full rounded-lg border border-slate-700/60 bg-slate-950 px-3 py-1.5 text-xs font-bold text-amber-400">
                    <input type="hidden" name="terms[${idx}][nominal]" id="termNominalVal_${idx}" value="${Math.round(term.nominal)}">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-bold uppercase text-slate-400">Jatuh Tempo</label>
                    <input type="date" name="terms[${idx}][due_date]" value="${term.due_date}" required
                        onchange="termItems[${idx}].due_date = this.value"
                        class="mt-1 block w-full rounded-lg border border-slate-700 bg-slate-900 px-2 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                </div>

                <div class="sm:col-span-1 flex items-end justify-center pt-4 sm:pt-0">
                    <button type="button" onclick="removeTermRow(${idx})" title="Hapus baris termin ini"
                        class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            `;
            termsContainer.appendChild(row);
        });

        updateTotalSummary();
    }

    function updateTotalSummary() {
        const contract = parseFloat(contractInput.value) || 0;
        let totalPct = 0;
        let totalNom = 0;

        termItems.forEach(t => {
            totalPct += (parseFloat(t.percentage) || 0);
            totalNom += (parseFloat(t.nominal) || 0);
        });

        termsTotalPct.innerText = totalPct.toFixed(1) + '%';
        termsTotalNominal.innerText = '(' + formatRupiah(totalNom) + ')';

        if (Math.round(totalPct) === 100) {
            termsTotalPct.className = 'font-black text-sm px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
            termsValidationMsg.className = 'text-[11px] text-emerald-400 font-semibold flex items-center gap-1.5';
            termsValidationMsg.innerHTML = `
                <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>Alokasi tepat 100% dari nilai kontrak (${termItems.length} termin). Siap dieksekusi.</span>
            `;
        } else {
            const diff = parseFloat((100 - totalPct).toFixed(1));
            termsTotalPct.className = 'font-black text-sm px-2 py-0.5 rounded-lg bg-amber-400/10 text-amber-400 border border-amber-400/20';
            termsValidationMsg.className = 'text-[11px] text-amber-400 font-semibold flex items-center gap-1.5';
            termsValidationMsg.innerHTML = `
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Total saat ini ${totalPct.toFixed(1)}% (${diff > 0 ? 'Kurang ' + diff + '%' : 'Kelebihan ' + Math.abs(diff) + '%'}). Sesuaikan persentase atau klik '⚡ Bagi Rata'.</span>
            `;
        }
    }

    function submitForm(actionValue) {
        document.getElementById('formAction').value = actionValue;
        document.getElementById('projectionForm').submit();
    }

    contractInput.addEventListener('input', calculateMargin);
    marginValueInput.addEventListener('input', calculateMargin);
    marginTypeInputs.forEach(r => r.addEventListener('change', calculateMargin));

    // Initial setup with 3 terms by default
    calculateMargin();
    applyPreset("3");
</script>
@endpush
@endsection
