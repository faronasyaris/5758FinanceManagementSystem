@extends('layouts.auth', ['title' => 'Aktivasi Google 2FA'])

@section('content')
<div class="text-center mb-6">
    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-400/10 text-amber-300 border border-amber-400/20 mb-3">
        <span>🔐 Setup Keamanan Wajib</span>
    </div>
    <h2 class="text-xl font-bold text-white tracking-tight">Aktivasi Google Authenticator</h2>
    <p class="text-xs text-slate-400 mt-1">
        Halo, <strong>{{ $user->name }}</strong> ({{ strtoupper($user->role) }}). Karena ini login pertama Anda, pindai QR Code di bawah menggunakan aplikasi <strong>Google Authenticator</strong> di ponsel Anda.
    </p>
</div>

<div class="flex flex-col items-center justify-center p-4 bg-white rounded-2xl shadow-inner mb-5">
    <div class="w-48 h-48 flex items-center justify-center">
        {!! $qrCodeSvg !!}
    </div>
    <span class="text-[11px] text-slate-600 font-medium mt-2">Pindai barcode di atas via kamera aplikasi</span>
</div>

<div class="mb-5 p-3 rounded-xl bg-slate-800/60 border border-slate-700/60 text-center">
    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-1">Kunci Manual (Jika tidak bisa scan)</div>
    <code class="text-xs text-amber-300 font-mono tracking-widest select-all bg-slate-950/60 px-2.5 py-1 rounded border border-slate-700/80">{{ $secret }}</code>
</div>

<form method="POST" action="{{ route('2fa.setup') }}" class="space-y-4">
    @csrf

    <div>
        <label for="one_time_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 text-center">
            Masukkan 6 Angka Kode OTP
        </label>
        <div class="mt-2">
            <input id="one_time_password" name="one_time_password" type="text" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required autofocus
                placeholder="123456"
                class="block w-full text-center text-2xl font-mono tracking-[0.5em] rounded-xl border border-slate-700 bg-slate-800/90 px-4 py-2.5 text-white placeholder-slate-600 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30">
        </div>
        <p class="text-[11px] text-slate-400 text-center mt-1.5">
            Kode berganti otomatis setiap 30 detik di ponsel Anda.
        </p>
    </div>

    <button type="submit"
        class="w-full flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-bold text-slate-950 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 shadow-lg shadow-amber-500/20 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-amber-400/30">
        Verifikasi & Masuk ke Dasbor
    </button>
</form>
@endsection
