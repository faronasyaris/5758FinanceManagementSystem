<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Project;
use App\Models\Projection;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectionController extends Controller
{
    /**
     * Display a listing of projections and draft simulations.
     */
    public function index(): View
    {
        $draftProjects = Project::where('status', Project::STATUS_DRAFT)
            ->with(['client', 'projection'])
            ->latest()
            ->get();

        $activeProjects = Project::where('status', Project::STATUS_ONGOING)
            ->with(['client', 'projection'])
            ->latest()
            ->get();

        return view('projections.index', [
            'draftProjects' => $draftProjects,
            'activeProjects' => $activeProjects,
        ]);
    }

    /**
     * Show the interactive Smart Margin & Auto-Termin calculator form.
     */
    public function create(): View
    {
        $clients = Client::orderBy('name')->get();

        return view('projections.create', [
            'clients' => $clients,
        ]);
    }

    /**
     * Store projection simulation (saves project in draft and projection data).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'project_name' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'total_contract_value' => ['required', 'numeric', 'min:1'],
            'target_margin_type' => ['required', 'in:percentage,nominal'],
            'target_margin_value' => ['required', 'numeric', 'min:0'],
            'period_month' => ['nullable', 'integer', 'between:1,12'],
            'period_year' => ['nullable', 'integer', 'min:2020'],
            'action' => ['required', 'in:save_draft,execute_now'],
            // Termin terms
            'terms' => ['nullable', 'array'],
            'terms.*.name' => ['nullable', 'string'],
            'terms.*.percentage' => ['nullable', 'numeric'],
            'terms.*.nominal' => ['nullable', 'numeric'],
            'terms.*.due_date' => ['nullable', 'date'],
        ]);

        $contractValue = (float) $validated['total_contract_value'];
        $marginType = $validated['target_margin_type'];
        $marginValue = (float) $validated['target_margin_value'];

        $estimatedExpense = Projection::calculateEstimatedExpense($contractValue, $marginType, $marginValue);

        return DB::transaction(function () use ($validated, $contractValue, $marginType, $marginValue, $estimatedExpense) {
            $isExecuteNow = $validated['action'] === 'execute_now';

            $project = Project::create([
                'client_id' => $validated['client_id'],
                'name' => $validated['project_name'],
                'status' => $isExecuteNow ? Project::STATUS_ONGOING : Project::STATUS_DRAFT,
                'start_date' => $validated['start_date'] ?? now()->toDateString(),
                'end_date' => $validated['end_date'],
            ]);

            $projection = Projection::create([
                'project_id' => $project->id,
                'total_contract_value' => $contractValue,
                'target_margin_type' => $marginType,
                'target_margin_value' => $marginValue,
                'estimated_expense' => $estimatedExpense,
                'period_month' => $validated['period_month'] ?? now()->month,
                'period_year' => $validated['period_year'] ?? now()->year,
            ]);

            // If terms are provided and project is executed immediately
            if ($isExecuteNow && !empty($validated['terms'])) {
                $this->createTerminTransactions($project, $validated['terms'], $contractValue);
            }

            if ($isExecuteNow) {
                return redirect()->route('projects.show', $project)->with('success', "Proyek '{$project->name}' berhasil dijalankan! Jadwal termin telah masuk sebagai Piutang Pending.");
            }

            return redirect()->route('projections.index')->with('success', "Simulasi proyek '{$project->name}' berhasil disimpan sebagai Draft.");
        });
    }

    /**
     * Execute a draft project (Jalankan Proyek) and generate pending termin transactions.
     */
    public function execute(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'terms' => ['required', 'array', 'min:1'],
            'terms.*.name' => ['required', 'string'],
            'terms.*.percentage' => ['nullable', 'numeric'],
            'terms.*.nominal' => ['required', 'numeric', 'min:1'],
            'terms.*.due_date' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($project, $validated) {
            $project->update(['status' => Project::STATUS_ONGOING]);

            $contractValue = $project->projection ? (float) $project->projection->total_contract_value : 0;
            $this->createTerminTransactions($project, $validated['terms'], $contractValue);
        });

        return redirect()->route('projects.show', $project)->with('success', "Proyek '{$project->name}' resmi berstatus ONGOING! Jadwal termin otomatis tersimpan sebagai Piutang.");
    }

    /**
     * Helper to create pending termin records in transactions.
     */
    private function createTerminTransactions(Project $project, array $terms, float $contractValue): void
    {
        $category = Category::where('type', Category::TYPE_IN)->first();

        foreach ($terms as $index => $term) {
            $nominal = !empty($term['nominal']) ? (float) $term['nominal'] : ($contractValue * ((float) ($term['percentage'] ?? 0) / 100));
            $termName = $term['name'] ?? ('Termin ' . ($index + 1));

            Transaction::create([
                'project_id' => $project->id,
                'category_id' => $category ? $category->id : 1,
                'account_id' => null, // Pending has no account assigned yet!
                'type' => Transaction::TYPE_IN,
                'nominal' => $nominal,
                'transaction_date' => now()->toDateString(),
                'due_date' => $term['due_date'] ?? null,
                'payment_status' => Transaction::STATUS_PENDING,
                'description' => "Tagihan {$termName} - {$project->name}",
            ]);
        }
    }
}
