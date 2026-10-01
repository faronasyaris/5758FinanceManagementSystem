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
use Tests\TestCase;

class StepOneDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_creates_three_strict_users_with_null_2fa(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 3);

        $owner = User::where('email', 'bos@5758inc.com')->first();
        $this->assertNotNull($owner);
        $this->assertEquals(User::ROLE_OWNER, $owner->role);
        $this->assertTrue($owner->isOwner());
        $this->assertFalse($owner->isAdmin());
        $this->assertNull($owner->google2fa_secret);
        $this->assertFalse($owner->hasTwoFactorSetup());

        $admin1 = User::where('email', 'admin1@5758inc.com')->first();
        $this->assertNotNull($admin1);
        $this->assertEquals(User::ROLE_ADMIN, $admin1->role);
        $this->assertTrue($admin1->isAdmin());
        $this->assertFalse($admin1->isOwner());
        $this->assertNull($admin1->google2fa_secret);

        $admin2 = User::where('email', 'admin2@5758inc.com')->first();
        $this->assertNotNull($admin2);
        $this->assertEquals(User::ROLE_ADMIN, $admin2->role);
        $this->assertTrue($admin2->isAdmin());
        $this->assertNull($admin2->google2fa_secret);
    }

    public function test_relationships_and_project_margin_calculations(): void
    {
        $client = Client::create([
            'name' => 'PT Maju Terus',
            'contact_info' => '08123456789',
            'address' => 'Jakarta Selatan',
        ]);

        $account = Account::create([
            'name' => 'BCA Perusahaan',
            'initial_balance' => 50000000,
            'current_balance' => 50000000,
        ]);

        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Pembangunan Kantor Cabang',
            'status' => Project::STATUS_ONGOING,
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-31',
        ]);

        $catIn = Category::create([
            'name' => 'Termin Proyek',
            'type' => Category::TYPE_IN,
        ]);

        $catOut = Category::create([
            'name' => 'Material Konstruksi',
            'type' => Category::TYPE_OUT,
        ]);

        // Paid income: 30,000,000
        $t1 = Transaction::create([
            'project_id' => $project->id,
            'category_id' => $catIn->id,
            'account_id' => $account->id,
            'type' => Transaction::TYPE_IN,
            'nominal' => 30000000,
            'transaction_date' => '2026-10-01',
            'payment_status' => Transaction::STATUS_PAID,
            'description' => 'DP 30%',
        ]);

        // Pending income: 70,000,000
        $t2 = Transaction::create([
            'project_id' => $project->id,
            'category_id' => $catIn->id,
            'account_id' => null,
            'type' => Transaction::TYPE_IN,
            'nominal' => 70000000,
            'transaction_date' => '2026-10-01',
            'due_date' => '2026-11-15',
            'payment_status' => Transaction::STATUS_PENDING,
            'description' => 'Pelunasan 70%',
        ]);

        // Paid expense: 20,000,000
        $t3 = Transaction::create([
            'project_id' => $project->id,
            'category_id' => $catOut->id,
            'account_id' => $account->id,
            'type' => Transaction::TYPE_OUT,
            'nominal' => 20000000,
            'transaction_date' => '2026-10-02',
            'payment_status' => Transaction::STATUS_PAID,
            'description' => 'Beli Semen & Pasir',
        ]);

        // Pending expense: 10,000,000
        $t4 = Transaction::create([
            'project_id' => $project->id,
            'category_id' => $catOut->id,
            'account_id' => null,
            'type' => Transaction::TYPE_OUT,
            'nominal' => 10000000,
            'transaction_date' => '2026-10-02',
            'due_date' => '2026-10-25',
            'payment_status' => Transaction::STATUS_PENDING,
            'description' => 'Tagihan Subkon Belum Dibayar',
        ]);

        // Check Project Calculations:
        // Total Income: 30jt + 70jt = 100jt
        // Total Expense: 20jt + 10jt = 30jt
        // Margin: 100jt - 30jt = 70jt
        $this->assertEquals(100000000, $project->total_income);
        $this->assertEquals(30000000, $project->total_expense);
        $this->assertEquals(70000000, $project->project_margin);
        $this->assertTrue($project->isProfitable());

        // Realized values
        $this->assertEquals(30000000, $project->realized_income);
        $this->assertEquals(20000000, $project->realized_expense);
        $this->assertEquals(10000000, $project->realized_margin);

        // Pending values
        $this->assertEquals(70000000, $project->pending_income);
        $this->assertEquals(10000000, $project->pending_expense);

        // Account balance recalculation:
        // Initial 50jt + Paid In (30jt) - Paid Out (20jt) = 60jt
        $account->recalculateBalance();
        $this->assertEquals(60000000, (float) $account->current_balance);

        // Relationships
        $this->assertEquals('PT Maju Terus', $project->client->name);
        $this->assertEquals(4, $project->transactions()->count());
        $this->assertEquals(1, $client->projects()->count());
    }

    public function test_smart_margin_projection_calculation(): void
    {
        $client = Client::create(['name' => 'Klien Test']);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Proyek Simulasi',
            'status' => Project::STATUS_DRAFT,
        ]);

        // Total contract: 100,000,000. Target margin: 15% -> Estimated expense ceiling: 85,000,000
        $estimatedExpense = Projection::calculateEstimatedExpense(100000000, Projection::MARGIN_TYPE_PERCENTAGE, 15);
        $this->assertEquals(85000000, $estimatedExpense);

        $projection = Projection::create([
            'project_id' => $project->id,
            'total_contract_value' => 100000000,
            'target_margin_type' => Projection::MARGIN_TYPE_PERCENTAGE,
            'target_margin_value' => 15,
            'estimated_expense' => $estimatedExpense,
            'period_month' => 10,
            'period_year' => 2026,
        ]);

        $this->assertEquals(15000000, $projection->target_profit_nominal);
        $this->assertEquals(15, $projection->target_profit_percentage);
        $this->assertEquals($project->id, $projection->project->id);
    }
}
