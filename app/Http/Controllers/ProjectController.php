<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $query = Project::with(['client', 'projection'])->withCount('transactions');

        if ($status && in_array($status, [Project::STATUS_DRAFT, Project::STATUS_ONGOING, Project::STATUS_COMPLETED])) {
            $query->where('status', $status);
        }

        $projects = $query->latest()->get();

        return view('projects.index', [
            'projects' => $projects,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Show form to create a project.
     */
    public function create(): View
    {
        $clients = Client::orderBy('name')->get();

        return view('projects.create', [
            'clients' => $clients,
        ]);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,ongoing,completed'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $project = Project::create($validated);

        return redirect()->route('projects.show', $project)->with('success', "Proyek '{$project->name}' berhasil dibuat.");
    }

    /**
     * Display the specified project with financial rapor and transaction history.
     */
    public function show(Project $project): View
    {
        $project->load(['client', 'projection', 'transactions.category', 'transactions.account']);
        $transactions = $project->transactions()->orderBy('transaction_date', 'desc')->get();

        return view('projects.show', [
            'project' => $project,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(Project $project): View
    {
        $clients = Client::orderBy('name')->get();

        return view('projects.edit', [
            'project' => $project,
            'clients' => $clients,
        ]);
    }

    /**
     * Update project.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,ongoing,completed'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)->with('success', "Data proyek berhasil diperbarui.");
    }

    /**
     * Delete project if needed.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', "Proyek berhasil dihapus.");
    }
}
