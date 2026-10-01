<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Client;
use App\Models\Project;
use App\Models\Projection;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultPassword = 'password'; // Default password untuk setup awal

        // 1. Eksekutif / Owner (1 akun)
        User::firstOrCreate(
            ['email' => 'bos@5758inc.com'],
            [
                'name' => 'Owner 5758inc',
                'password' => Hash::make($defaultPassword),
                'role' => User::ROLE_OWNER,
                'google2fa_secret' => null, // null memaksa registrasi 2FA QR code pada first login
            ]
        );

        // 2. Admin Keuangan 1
        User::firstOrCreate(
            ['email' => 'admin1@5758inc.com'],
            [
                'name' => 'Admin Keuangan 1',
                'password' => Hash::make($defaultPassword),
                'role' => User::ROLE_ADMIN,
                'google2fa_secret' => null,
            ]
        );

        // 3. Admin Keuangan 2
        User::firstOrCreate(
            ['email' => 'admin2@5758inc.com'],
            [
                'name' => 'Admin Keuangan 2',
                'password' => Hash::make($defaultPassword),
                'role' => User::ROLE_ADMIN,
                'google2fa_secret' => null,
            ]
        );

        // Starter categories bebas jargon akuntansi
        $starterCategories = [
            ['name' => 'Uang Muka (DP) Proyek', 'type' => Category::TYPE_IN],
            ['name' => 'Pelunasan / Termin Klien', 'type' => Category::TYPE_IN],
            ['name' => 'Pendapatan Lain-lain', 'type' => Category::TYPE_IN],
            ['name' => 'Honor & Upah Lapangan', 'type' => Category::TYPE_OUT],
            ['name' => 'Material & Vendor', 'type' => Category::TYPE_OUT],
            ['name' => 'Operasional & Transport', 'type' => Category::TYPE_OUT],
            ['name' => 'Pajak & Retribusi', 'type' => Category::TYPE_OUT],
            ['name' => 'Kasbon Tim / Karyawan', 'type' => Category::TYPE_OUT],
        ];

        foreach ($starterCategories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name'], 'type' => $category['type']]
            );
        }

        // Sumber Kas (Multi-Rekening) Awal
        $bca = Account::firstOrCreate(
            ['name' => 'Rekening BCA Perusahaan'],
            ['initial_balance' => 100000000, 'current_balance' => 100000000]
        );

        $mandiri = Account::firstOrCreate(
            ['name' => 'Rekening Mandiri Operasional'],
            ['initial_balance' => 25000000, 'current_balance' => 25000000]
        );

        $brankas = Account::firstOrCreate(
            ['name' => 'Kas Brankas Tunai'],
            ['initial_balance' => 5000000, 'current_balance' => 5000000]
        );

        // Starter Klien & Proyek Percontohan
        $client = Client::firstOrCreate(
            ['name' => 'PT Nusantara Graha Mandiri'],
            ['contact_info' => '0812-9876-5432 (Bpk. Bambang)', 'address' => 'Gedung Wisma Antara Lt. 8, Jakarta Pusat']
        );

        $project = Project::firstOrCreate(
            ['name' => 'Pengadaan Infrastruktur Jaringan & Server 5758'],
            [
                'client_id' => $client->id,
                'status' => Project::STATUS_ONGOING,
                'start_date' => now()->subDays(15)->toDateString(),
                'end_date' => now()->addDays(45)->toDateString(),
            ]
        );

        $catDp = Category::where('name', 'Uang Muka (DP) Proyek')->first();
        $catTermin = Category::where('name', 'Pelunasan / Termin Klien')->first();
        $catMaterial = Category::where('name', 'Material & Vendor')->first();
        $catHonor = Category::where('name', 'Honor & Upah Lapangan')->first();

        // Projection
        Projection::firstOrCreate(
            ['project_id' => $project->id],
            [
                'total_contract_value' => 150000000,
                'target_margin_type' => Projection::MARGIN_TYPE_PERCENTAGE,
                'target_margin_value' => 20, // 20% profit = 30jt
                'estimated_expense' => 120000000, // Pagu batas modal = 120jt
                'period_month' => now()->month,
                'period_year' => now()->year,
            ]
        );

        // Transactions for sample project
        // 1. DP Cair (Paid In): 45 Juta ke BCA
        if (!Transaction::where('project_id', $project->id)->where('description', 'Pembayaran DP 30%')->exists()) {
            Transaction::create([
                'project_id' => $project->id,
                'category_id' => $catDp->id,
                'account_id' => $bca->id,
                'type' => Transaction::TYPE_IN,
                'nominal' => 45000000,
                'transaction_date' => now()->subDays(10)->toDateString(),
                'payment_status' => Transaction::STATUS_PAID,
                'description' => 'Pembayaran DP 30%',
            ]);
            $bca->increment('current_balance', 45000000);
        }

        // 2. Termin 1 Belum Cair (Pending In): 60 Juta
        if (!Transaction::where('project_id', $project->id)->where('description', 'Tagihan Termin 1 (40%) - Fisik 50%')->exists()) {
            Transaction::create([
                'project_id' => $project->id,
                'category_id' => $catTermin->id,
                'account_id' => null,
                'type' => Transaction::TYPE_IN,
                'nominal' => 60000000,
                'transaction_date' => now()->subDays(2)->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'payment_status' => Transaction::STATUS_PENDING,
                'description' => 'Tagihan Termin 1 (40%) - Fisik 50%',
            ]);
        }

        // 3. Pelunasan Belum Cair (Pending In): 45 Juta
        if (!Transaction::where('project_id', $project->id)->where('description', 'Pelunasan Akhir (30%)')->exists()) {
            Transaction::create([
                'project_id' => $project->id,
                'category_id' => $catTermin->id,
                'account_id' => null,
                'type' => Transaction::TYPE_IN,
                'nominal' => 45000000,
                'transaction_date' => now()->subDays(2)->toDateString(),
                'due_date' => now()->addDays(40)->toDateString(),
                'payment_status' => Transaction::STATUS_PENDING,
                'description' => 'Pelunasan Akhir (30%)',
            ]);
        }

        // 4. Pembelian Material (Paid Out): 25 Juta dari BCA
        if (!Transaction::where('project_id', $project->id)->where('description', 'Pembelian Switch, Router & Rak Server')->exists()) {
            Transaction::create([
                'project_id' => $project->id,
                'category_id' => $catMaterial->id,
                'account_id' => $bca->id,
                'type' => Transaction::TYPE_OUT,
                'nominal' => 25000000,
                'transaction_date' => now()->subDays(8)->toDateString(),
                'payment_status' => Transaction::STATUS_PAID,
                'description' => 'Pembelian Switch, Router & Rak Server',
            ]);
            $bca->decrement('current_balance', 25000000);
        }

        // 5. Kasbon Staf yang Terlambat (Overdue Pending Out): 3 Juta
        if (!Transaction::where('project_id', $project->id)->where('description', 'Kasbon Belanja Tambahan Kabel UTP (Overdue)')->exists()) {
            Transaction::create([
                'project_id' => $project->id,
                'category_id' => $catHonor->id,
                'account_id' => null,
                'type' => Transaction::TYPE_OUT,
                'nominal' => 3000000,
                'transaction_date' => now()->subDays(12)->toDateString(),
                'due_date' => now()->subDays(3)->toDateString(), // OVERDUE for testing Radar Alert!
                'payment_status' => Transaction::STATUS_PENDING,
                'description' => 'Kasbon Belanja Tambahan Kabel UTP (Overdue)',
            ]);
        }
    }
}
