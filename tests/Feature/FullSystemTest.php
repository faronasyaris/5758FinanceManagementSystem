<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Client;
use App\Models\Project;
use App\Models\Projection;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class FullSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_registration_is_strictly_disabled(): void
    {
        $response = $this->get('/register');
        $response->assertNotFound();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_first_login_redirects_to_2fa_setup_with_qr_code(): void
    {
        $owner = User::where('email', 'bos@5758inc.com')->first();
        $this->assertNull($owner->google2fa_secret);

        $loginResponse = $this->post('/login', [
            'email' => 'bos@5758inc.com',
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect(route('2fa.setup'));

        $setupResponse = $this->get('/2fa/setup');
        $setupResponse->assertStatus(200);
        $setupResponse->assertSee('Aktivasi Google Authenticator');
        $setupResponse->assertSee('svg', false); // Confirms QR Code SVG exists in HTML

        // Complete 2FA setup with valid OTP
        $secret = session('auth.2fa.secret');
        $this->assertNotEmpty($secret);

        $google2fa = new Google2FA();
        $otp = $google2fa->getCurrentOtp($secret);

        $verifyResponse = $this->post('/2fa/setup', [
            'one_time_password' => $otp,
        ]);

        $verifyResponse->assertRedirect(route('dashboard'));

        $owner->refresh();
        $this->assertEquals($secret, $owner->google2fa_secret);
        $this->assertTrue(session('auth.2fa.authenticated'));
    }

    public function test_subsequent_login_prompts_for_otp_only(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $admin = User::where('email', 'admin1@5758inc.com')->first();
        $admin->update(['google2fa_secret' => $secret]);

        $loginResponse = $this->post('/login', [
            'email' => 'admin1@5758inc.com',
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect(route('2fa.verify'));

        $verifyPage = $this->get('/2fa/verify');
        $verifyPage->assertStatus(200);
        $verifyPage->assertSee('Kode Verifikasi OTP');

        // Submit invalid OTP
        $failResponse = $this->post('/2fa/verify', [
            'one_time_password' => '000000',
        ]);
        $failResponse->assertSessionHasErrors('one_time_password');

        // Submit valid OTP
        $otp = $google2fa->getCurrentOtp($secret);
        $successResponse = $this->post('/2fa/verify', [
            'one_time_password' => $otp,
        ]);
        $successResponse->assertRedirect(route('dashboard'));
    }

    public function test_master_data_crud_and_cashflow_balance_lifecycle(): void
    {
        $owner = User::where('email', 'bos@5758inc.com')->first();
        $this->actingAs($owner);
        session(['auth.2fa.authenticated' => true]);

        // 1. Create Account
        $accResponse = $this->post(route('accounts.store'), [
            'name' => 'BCA Operasional',
            'initial_balance' => 100000000,
        ]);
        $accResponse->assertRedirect(route('accounts.index'));
        $account = Account::where('name', 'BCA Operasional')->first();
        $this->assertEquals(100000000, (float) $account->current_balance);

        // 2. Create Client
        $clientResponse = $this->post(route('clients.store'), [
            'name' => 'PT Mega Pratama',
            'contact_info' => '0812345678',
            'address' => 'Surabaya',
        ]);
        $clientResponse->assertRedirect(route('clients.index'));
        $client = Client::where('name', 'PT Mega Pratama')->first();

        // 3. Create Project
        $projResponse = $this->post(route('projects.store'), [
            'client_id' => $client->id,
            'name' => 'Instalasi Jaringan Cabang',
            'status' => 'ongoing',
            'start_date' => '2026-10-01',
        ]);
        $project = Project::where('name', 'Instalasi Jaringan Cabang')->first();

        $catIn = Category::where('type', 'in')->first();
        $catOut = Category::where('type', 'out')->first();

        // 4. Create Paid Income -> Increases account balance
        $this->post(route('transactions.store'), [
            'project_id' => $project->id,
            'category_id' => $catIn->id,
            'type' => 'in',
            'nominal' => 25000000,
            'transaction_date' => '2026-10-01',
            'payment_status' => 'paid',
            'account_id' => $account->id,
            'description' => 'DP 25 Juta',
        ]);

        $account->refresh();
        $this->assertEquals(125000000, (float) $account->current_balance);

        // 5. Create Paid Expense -> Decreases account balance
        $this->post(route('transactions.store'), [
            'project_id' => $project->id,
            'category_id' => $catOut->id,
            'type' => 'out',
            'nominal' => 10000000,
            'transaction_date' => '2026-10-02',
            'payment_status' => 'paid',
            'account_id' => $account->id,
            'description' => 'Beli Alat & Kabel',
        ]);

        $account->refresh();
        $this->assertEquals(115000000, (float) $account->current_balance);

        // 6. Create Pending Income (Piutang) -> Does not touch account balance yet
        $this->post(route('transactions.store'), [
            'project_id' => $project->id,
            'category_id' => $catIn->id,
            'type' => 'in',
            'nominal' => 30000000,
            'transaction_date' => '2026-10-02',
            'payment_status' => 'pending',
            'due_date' => '2026-10-20',
            'description' => 'Termin 2 Belum Cair',
        ]);

        $account->refresh();
        $this->assertEquals(115000000, (float) $account->current_balance);

        $pendingTx = Transaction::where('description', 'Termin 2 Belum Cair')->first();
        $this->assertEquals('pending', $pendingTx->payment_status);

        // 7. Mark as Paid (Pelunasan Termin) -> Status becomes paid, account balance updates
        $this->post(route('transactions.mark-as-paid', $pendingTx), [
            'account_id' => $account->id,
            'settlement_date' => '2026-10-05',
        ]);

        $pendingTx->refresh();
        $this->assertEquals('paid', $pendingTx->payment_status);
        $this->assertEquals($account->id, $pendingTx->account_id);

        $account->refresh();
        $this->assertEquals(145000000, (float) $account->current_balance);
    }

    public function test_smart_margin_and_auto_termin_execution(): void
    {
        $owner = User::where('email', 'bos@5758inc.com')->first();
        $this->actingAs($owner);
        session(['auth.2fa.authenticated' => true]);

        $client = Client::create(['name' => 'Klien Proyeksi']);

        // Execute projection simulation with 3-termin auto generation
        $response = $this->post(route('projections.store'), [
            'client_id' => $client->id,
            'project_name' => 'Pengadaan Sistem IT 2026',
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-31',
            'total_contract_value' => 150000000,
            'target_margin_type' => 'percentage',
            'target_margin_value' => 20, // 20% profit = 30jt, budget ceiling = 120jt
            'action' => 'execute_now',
            'terms' => [
                ['name' => 'DP 30%', 'percentage' => 30, 'nominal' => 45000000, 'due_date' => '2026-10-10'],
                ['name' => 'Termin 1 (40%)', 'percentage' => 40, 'nominal' => 60000000, 'due_date' => '2026-11-10'],
                ['name' => 'Pelunasan (30%)', 'percentage' => 30, 'nominal' => 45000000, 'due_date' => '2026-12-10'],
            ],
        ]);

        $project = Project::where('name', 'Pengadaan Sistem IT 2026')->first();
        $this->assertNotNull($project);
        $this->assertEquals('ongoing', $project->status);

        $projection = $project->projection;
        $this->assertNotNull($projection);
        $this->assertEquals(120000000, (float) $projection->estimated_expense);
        $this->assertEquals(30000000, (float) $projection->target_profit_nominal);

        // 3 pending transactions generated as receivables
        $this->assertEquals(3, $project->transactions()->where('payment_status', 'pending')->count());
        $this->assertEquals(150000000, $project->pending_income);
    }

    public function test_dashboard_and_report_generator_access(): void
    {
        $owner = User::where('email', 'bos@5758inc.com')->first();
        $this->actingAs($owner);
        session(['auth.2fa.authenticated' => true]);

        // Dashboard renders
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dasbor Eksekutif 5758inc');

        // Reports render
        $reportsRealisasi = $this->get('/reports?type=realisasi');
        $reportsRealisasi->assertStatus(200);
        $reportsRealisasi->assertSee('Laporan Realisasi Arus Kas');

        $reportsProyeksi = $this->get('/reports?type=proyeksi');
        $reportsProyeksi->assertStatus(200);
        $reportsProyeksi->assertSee('Laporan Proyeksi');

        // CSV Export
        $csvResponse = $this->get('/reports/export-csv?type=realisasi');
        $csvResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvResponse->headers->get('content-type'));
    }
}
