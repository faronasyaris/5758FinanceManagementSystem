<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Project;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show Executive Command Center Dashboard (Dasbor Eksekutif).
     */
    public function index(Request $request): View
    {
        $timeframe = $request->query('timeframe', 'bulanan'); // bulanan, triwulan, semester, tahunan, all
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $quarter = (int) $request->query('quarter', ceil(now()->month / 3));
        $semester = (int) $request->query('semester', now()->month <= 6 ? 1 : 2);

        // Determine date range for transaction metrics
        $startDate = null;
        $endDate = null;

        switch ($timeframe) {
            case 'bulanan':
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                $periodLabel = $startDate->translatedFormat('F Y');
                break;
            case 'triwulan':
                $startMonth = ($quarter - 1) * 3 + 1;
                $startDate = Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($year, $startMonth + 2, 1)->endOfMonth();
                $periodLabel = "Triwulan {$quarter} (Q{$quarter}) {$year}";
                break;
            case 'semester':
                $startMonth = ($semester === 1) ? 1 : 7;
                $startDate = Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($year, $startMonth + 5, 1)->endOfMonth();
                $periodLabel = "Semester {$semester} {$year}";
                break;
            case 'tahunan':
                $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
                $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
                $periodLabel = "Tahun {$year}";
                break;
            case 'all':
            default:
                $periodLabel = 'Seluruh Periode (Kumulatif)';
                break;
        }

        // 1. Total Saldo Saat Ini (Real Cash held right now across all accounts)
        $accounts = Account::orderBy('name')->get();
        $totalCurrentCash = $accounts->sum('current_balance');

        // Query transactions within chosen timeframe (or all)
        $txQuery = Transaction::query();
        if ($startDate && $endDate) {
            $txQuery->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()]);
        }

        // Realized Cash Flow in Period
        $periodRealizedIn = (clone $txQuery)->paid()->income()->sum('nominal');
        $periodRealizedOut = (clone $txQuery)->paid()->expense()->sum('nominal');
        $periodNetCash = $periodRealizedIn - $periodRealizedOut;

        // 2. Piutang & Hutang / Kasbon
        // Pending Income = Uang di luar (tagihan termin belum cair)
        $totalPendingIncome = Transaction::pending()->income()->sum('nominal');
        // Pending Expense = Kewajiban / Kasbon staf belum kembali
        $totalPendingExpense = Transaction::pending()->expense()->sum('nominal');

        // 3. Overdue Alerts (Radar Otomatis: Belum Cair & Melewati Due Date)
        $overdueAlerts = Transaction::with(['project.client', 'category'])
            ->overdue()
            ->orderBy('due_date', 'asc')
            ->get();

        // 4. Rapor Kegiatan (Ongoing Projects)
        $ongoingProjects = Project::where('status', Project::STATUS_ONGOING)
            ->with(['client', 'projection'])
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client_name' => $project->client ? $project->client->name : '-',
                    'contract_value' => $project->projection ? (float) $project->projection->total_contract_value : 0,
                    'estimated_expense' => $project->projection ? (float) $project->projection->estimated_expense : 0,
                    'total_income' => $project->total_income,
                    'total_expense' => $project->total_expense,
                    'project_margin' => $project->project_margin,
                    'is_profitable' => $project->isProfitable(),
                    'realized_income' => $project->realized_income,
                    'realized_expense' => $project->realized_expense,
                ];
            });

        return view('dashboard.index', [
            'accounts' => $accounts,
            'totalCurrentCash' => $totalCurrentCash,
            'periodRealizedIn' => $periodRealizedIn,
            'periodRealizedOut' => $periodRealizedOut,
            'periodNetCash' => $periodNetCash,
            'totalPendingIncome' => $totalPendingIncome,
            'totalPendingExpense' => $totalPendingExpense,
            'overdueAlerts' => $overdueAlerts,
            'ongoingProjects' => $ongoingProjects,
            'timeframe' => $timeframe,
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter,
            'semester' => $semester,
            'periodLabel' => $periodLabel,
        ]);
    }
}
