<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetTwoFactorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '2fa:reset 
                            {email? : Email pengguna yang ingin di-reset (misal: bos@5758inc.com)} 
                            {--all : Reset Google Authenticator untuk semua pengguna}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset Google Authenticator (2FA) untuk satu atau seluruh pengguna sistem';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=========================================');
        $this->info('  5758inc - Reset Google Authenticator   ');
        $this->info('=========================================');

        // Reset all users
        if ($this->option('all')) {
            if (!$this->confirm('Apakah Anda yakin ingin me-reset 2FA untuk SEMUA pengguna?', true)) {
                $this->warn('Operasi dibatalkan.');
                return self::SUCCESS;
            }

            $count = User::query()->update(['google2fa_secret' => null]);
            $this->info("Berhasil me-reset Google Authenticator untuk {$count} pengguna.");
            $this->comment('Saat login berikutnya, seluruh pengguna akan diminta scan QR Code baru.');
            $this->displayUserTable();
            return self::SUCCESS;
        }

        // Email provided as argument
        $email = $this->argument('email');

        // Interactive choice if no email provided
        if (empty($email)) {
            $users = User::all();
            if ($users->isEmpty()) {
                $this->error('Tidak ada pengguna yang terdaftar di database.');
                return self::FAILURE;
            }

            $choices = $users->map(function ($u) {
                $status = !empty($u->google2fa_secret) ? '[2FA Aktif]' : '[2FA Belum Diatur]';
                return "{$u->email} ({$u->name} - {$u->role}) {$status}";
            })->toArray();

            $choices[] = 'Batalkan';

            $selected = $this->choice(
                'Pilih pengguna yang ingin di-reset Google Authenticator-nya:',
                $choices,
                0
            );

            if ($selected === 'Batalkan') {
                $this->warn('Operasi dibatalkan.');
                return self::SUCCESS;
            }

            // Extract email from chosen string
            $email = explode(' ', $selected)[0];
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Pengguna dengan email '{$email}' tidak ditemukan di sistem.");
            return self::FAILURE;
        }

        $user->google2fa_secret = null;
        $user->save();

        $this->info("SUKSES: Google Authenticator untuk '{$user->name}' ({$user->email}) berhasil di-reset!");
        $this->line("Status 2FA: <fg=yellow>Di-reset (Menunggu scan QR Code baru saat login berikutnya)</>");
        $this->line("");
        $this->comment("Petunjuk login:");
        $this->line("1. Buka halaman login di browser (http://127.0.0.1:8000/login)");
        $this->line("2. Masukkan email '{$user->email}' dan kata sandi.");
        $this->line("3. Sistem akan otomatis menampilkan QR Code baru untuk di-scan via Google Authenticator.");

        return self::SUCCESS;
    }

    /**
     * Display quick status table of users.
     */
    private function displayUserTable(): void
    {
        $rows = User::all()->map(function ($u) {
            return [
                'ID' => $u->id,
                'Nama' => $u->name,
                'Email' => $u->email,
                'Role' => strtoupper($u->role),
                'Status 2FA' => empty($u->google2fa_secret) ? 'Perlu Setup QR' : 'Aktif',
            ];
        });

        $this->table(['ID', 'Nama', 'Email', 'Role', 'Status 2FA'], $rows);
    }
}
