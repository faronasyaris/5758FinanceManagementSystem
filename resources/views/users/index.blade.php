@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Keamanan 2FA')

@section('content')
<div class="space-y-6">

    <!-- Header & Info -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400/10 text-amber-400 border border-amber-400/20">
                    Owner Area (Eksekutif)
                </span>
                <span class="text-xs text-slate-500">Akses Tertutup</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight mt-1">Manajemen Pengguna & Keamanan 2FA</h1>
            <p class="text-xs text-slate-400 mt-0.5">Kelola status otentikasi Google Authenticator untuk akun Owner dan Admin.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400">Total Pengguna:</span>
            <span class="px-2.5 py-1 rounded-xl bg-slate-800 text-amber-400 font-bold text-xs border border-slate-700">
                {{ $users->count() }} Akun Terdaftar
            </span>
        </div>
    </div>

    <!-- Alert / Notice Card -->
    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 text-xs text-slate-300 space-y-2">
        <div class="flex items-center gap-2 font-bold text-amber-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Cara Kerja Reset Google Authenticator</span>
        </div>
        <p class="text-slate-400 leading-relaxed">
            Ketika 2FA di-reset, kunci rahasia (<code class="text-amber-300 font-mono text-[11px]">google2fa_secret</code>) pengguna akan dikosongkan. 
            Saat pengguna tersebut login kembali menggunakan email & password, sistem akan <strong>secara otomatis menampilkan layar Setup QR Code baru</strong>. 
            Pengguna cukup membuka aplikasi <strong>Google Authenticator</strong> di HP baru/lama, scan QR Code tersebut, dan memasukkan 6 digit kode OTP.
        </p>
        <div class="pt-2 border-t border-slate-800 flex flex-wrap items-center gap-2 text-[11px] text-slate-400">
            <span>Bisa juga di-reset via Terminal / CLI server:</span>
            <code class="px-2 py-0.5 rounded bg-slate-800 text-amber-300 font-mono">php artisan 2fa:reset {email}</code>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="rounded-2xl bg-slate-900/60 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Pengguna</th>
                        <th class="py-3.5 px-4">Role Akses</th>
                        <th class="py-3.5 px-4">Status 2FA (Google Authenticator)</th>
                        <th class="py-3.5 px-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach ($users as $user)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-black text-amber-400">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white flex items-center gap-2">
                                            {{ $user->name }}
                                            @if ($user->id === Auth::id())
                                                <span class="px-1.5 py-0.2 rounded text-[9px] bg-amber-400/20 text-amber-300 border border-amber-400/30 font-medium">Akun Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($user->isOwner())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold text-[10px] bg-amber-400/10 text-amber-400 border border-amber-400/30">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        Owner (Eksekutif)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold text-[10px] bg-blue-500/10 text-blue-400 border border-blue-500/30">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        Admin Operasional
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($user->hasTwoFactorSetup())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-semibold text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Aktif (Terverifikasi)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-semibold text-[10px] bg-amber-400/10 text-amber-400 border border-amber-400/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Belum Diatur (Perlu Scan QR)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form method="POST" action="{{ route('users.reset-2fa', $user) }}"
                                    onsubmit="return confirm('Apakah Anda yakin ingin me-reset Google Authenticator untuk pengguna {{ $user->name }} ({{ $user->email }})? Saat login berikutnya, pengguna harus men-scan ulang QR Code baru.');">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs bg-slate-800 hover:bg-rose-500/20 text-rose-400 border border-slate-700 hover:border-rose-500/40 transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        <span>Reset 2FA</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
