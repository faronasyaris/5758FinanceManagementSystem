<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Project;
use App\Models\Projection;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display Report Generator interface.
     */
    public function index(Request $request): View
    {
        $reportType = $request->query('type', 'realisasi'); // realisasi | proyeksi
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());
        $projectId = $request->query('project_id');

        // Data for Laporan Realisasi (Actual Cash Movement)
        $realisasiTransactions = collect();
        $realisasiSummary = [
            'total_in' => 0,
            'total_out' => 0,
            'net_cash' => 0,
        ];
        $projectGrouped = collect();

        if ($reportType === 'realisasi') {
            $query = Transaction::with(['project.client', 'category', 'account'])
                ->paid()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->orderBy('transaction_date', 'asc');

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            $realisasiTransactions = $query->get();

            $realisasiSummary['total_in'] = $realisasiTransactions->where('type', Transaction::TYPE_IN)->sum('nominal');
            $realisasiSummary['total_out'] = $realisasiTransactions->where('type', Transaction::TYPE_OUT)->sum('nominal');
            $realisasiSummary['net_cash'] = $realisasiSummary['total_in'] - $realisasiSummary['total_out'];

            // Group by Project
            $projectGrouped = $realisasiTransactions->groupBy(function ($t) {
                return $t->project ? $t->project->name : 'Non-Proyek';
            })->map(function ($items, $projectName) {
                $in = $items->where('type', Transaction::TYPE_IN)->sum('nominal');
                $out = $items->where('type', Transaction::TYPE_OUT)->sum('nominal');
                return [
                    'project_name' => $projectName,
                    'total_in' => $in,
                    'total_out' => $out,
                    'net' => $in - $out,
                    'items' => $items,
                ];
            });
        }

        // Data for Laporan Proyeksi (Forecast of upcoming revenues & margins)
        $projectionProjects = collect();
        $projectionSummary = [
            'total_contract' => 0,
            'total_target_margin' => 0,
            'total_budget_ceiling' => 0,
            'total_pending_receivables' => 0,
        ];

        if ($reportType === 'proyeksi') {
            $pQuery = Project::with(['client', 'projection', 'transactions']);

            if ($projectId) {
                $pQuery->where('id', $projectId);
            }

            $projectionProjects = $pQuery->latest()->get()->map(function ($project) {
                $contract = $project->projection ? (float) $project->projection->total_contract_value : 0;
                $profitNominal = $project->projection ? (float) $project->projection->target_profit_nominal : 0;
                $budgetCeiling = $project->projection ? (float) $project->projection->estimated_expense : 0;
                $pendingIn = $project->pending_income;

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client_name' => $project->client ? $project->client->name : '-',
                    'status' => $project->status,
                    'contract_value' => $contract,
                    'profit_nominal' => $profitNominal,
                    'budget_ceiling' => $budgetCeiling,
                    'pending_income' => $pendingIn,
                    'realized_income' => $project->realized_income,
                    'realized_expense' => $project->realized_expense,
                    'project_margin' => $project->project_margin,
                ];
            });

            $projectionSummary['total_contract'] = $projectionProjects->sum('contract_value');
            $projectionSummary['total_target_margin'] = $projectionProjects->sum('profit_nominal');
            $projectionSummary['total_budget_ceiling'] = $projectionProjects->sum('budget_ceiling');
            $projectionSummary['total_pending_receivables'] = $projectionProjects->sum('pending_income');
        }

        $allProjects = Project::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();

        return view('reports.index', [
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'projectId' => $projectId,
            'realisasiTransactions' => $realisasiTransactions,
            'realisasiSummary' => $realisasiSummary,
            'projectGrouped' => $projectGrouped,
            'projectionProjects' => $projectionProjects,
            'projectionSummary' => $projectionSummary,
            'allProjects' => $allProjects,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Export report data as Excel-compatible CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $reportType = $request->query('type', 'realisasi');
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());
        $projectId = $request->query('project_id');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"laporan-5758inc-{$reportType}-{$startDate}-sd-{$endDate}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($reportType, $startDate, $endDate, $projectId) {
            $output = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens with proper accents and currency symbols
            fputs($output, "\xEF\xBB\xBF");

            if ($reportType === 'realisasi') {
                fputcsv($output, ['LAPORAN REALISASI ARUS KAS - 5758inc Finance Management System']);
                fputcsv($output, ["Periode: {$startDate} s/d {$endDate}"]);
                fputcsv($output, []);
                fputcsv($output, ['Tanggal', 'Nama Kegiatan/Proyek', 'Kategori', 'Jenis', 'Nominal (Rp)', 'Sumber Kas', 'Keterangan']);

                $query = Transaction::with(['project', 'category', 'account'])
                    ->paid()
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->orderBy('transaction_date', 'asc');

                if ($projectId) {
                    $query->where('project_id', $projectId);
                }

                $transactions = $query->get();
                $totalIn = 0;
                $totalOut = 0;

                foreach ($transactions as $t) {
                    $inOut = $t->type === 'in' ? 'Uang Masuk' : 'Uang Keluar';
                    if ($t->type === 'in') {
                        $totalIn += $t->nominal;
                    } else {
                        $totalOut += $t->nominal;
                    }

                    fputcsv($output, [
                        $t->transaction_date->format('d/m/Y'),
                        $t->project ? $t->project->name : '-',
                        $t->category ? $t->category->name : '-',
                        $inOut,
                        $t->nominal,
                        $t->account ? $t->account->name : '-',
                        $t->description ?? '-',
                    ]);
                }

                fputcsv($output, []);
                fputcsv($output, ['TOTAL UANG MASUK', '', '', '', $totalIn]);
                fputcsv($output, ['TOTAL UANG KELUAR', '', '', '', $totalOut]);
                fputcsv($output, ['ARUS KAS BERSIH (NET)', '', '', '', $totalIn - $totalOut]);

            } else {
                fputcsv($output, ['LAPORAN PROYEKSI & TARGET KEUNTUNGAN - 5758inc Finance Management System']);
                fputcsv($output, ["Tanggal Cetak: " . now()->format('d/m/Y')]);
                fputcsv($output, []);
                fputcsv($output, ['Nama Kegiatan/Proyek', 'Klien', 'Status', 'Nilai Kontrak (Rp)', 'Target Keuntungan (Rp)', 'Pagu Batas Modal (Rp)', 'Uang Masuk Riil (Rp)', 'Pengeluaran Riil (Rp)', 'Sisa Piutang (Rp)']);

                $pQuery = Project::with(['client', 'projection']);
                if ($projectId) {
                    $pQuery->where('id', $projectId);
                }
                $projects = $pQuery->get();

                foreach ($projects as $p) {
                    $contract = $p->projection ? $p->projection->total_contract_value : 0;
                    $targetProfit = $p->projection ? $p->projection->target_profit_nominal : 0;
                    $budget = $p->projection ? $p->projection->estimated_expense : 0;

                    fputcsv($output, [
                        $p->name,
                        $p->client ? $p->client->name : '-',
                        strtoupper($p->status),
                        $contract,
                        $targetProfit,
                        $budget,
                        $p->realized_income,
                        $p->realized_expense,
                        $p->pending_income,
                    ]);
                }
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
