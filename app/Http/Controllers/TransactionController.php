<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display cashflow transactions list with flexible filters.
     */
    public function index(Request $request): View
    {
        $query = Transaction::with(['project.client', 'category', 'account'])->latest('transaction_date')->latest('id');

        // Filter by Type
        if ($request->filled('type') && in_array($request->type, [Transaction::TYPE_IN, Transaction::TYPE_OUT])) {
            $query->where('type', $request->type);
        }

        // Filter by Payment Status
        if ($request->filled('status') && in_array($request->status, [Transaction::STATUS_PAID, Transaction::STATUS_PENDING])) {
            $query->where('payment_status', $request->status);
        }

        // Filter by Overdue
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        // Filter by Project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by Account
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $transactions = $query->paginate(25)->withQueryString();

        // Calculate summary for current view
        $totalPaidIncome = (clone $query)->paid()->income()->sum('nominal');
        $totalPaidExpense = (clone $query)->paid()->expense()->sum('nominal');
        $totalPendingIncome = (clone $query)->pending()->income()->sum('nominal');
        $totalPendingExpense = (clone $query)->pending()->expense()->sum('nominal');

        $projects = Project::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('transactions.index', [
            'transactions' => $transactions,
            'projects' => $projects,
            'accounts' => $accounts,
            'categories' => $categories,
            'totalPaidIncome' => $totalPaidIncome,
            'totalPaidExpense' => $totalPaidExpense,
            'totalPendingIncome' => $totalPendingIncome,
            'totalPendingExpense' => $totalPendingExpense,
        ]);
    }

    /**
     * Show form to record cash movement.
     */
    public function create(): View
    {
        $projects = Project::whereIn('status', [Project::STATUS_ONGOING, Project::STATUS_DRAFT])->orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();
        $incomeCategories = Category::income()->orderBy('name')->get();
        $expenseCategories = Category::expense()->orderBy('name')->get();

        return view('transactions.create', [
            'projects' => $projects,
            'accounts' => $accounts,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * Store new transaction in database and adjust account balance if paid.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', 'in:in,out'],
            'nominal' => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'payment_status' => ['required', 'in:paid,pending'],
            'account_id' => ['nullable', 'required_if:payment_status,paid', 'exists:accounts,id'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($validated) {
            $transaction = Transaction::create($validated);

            // If status is paid, immediately update the account's balance
            if ($transaction->payment_status === Transaction::STATUS_PAID && $transaction->account_id) {
                $account = Account::findOrFail($transaction->account_id);
                if ($transaction->type === Transaction::TYPE_IN) {
                    $account->increment('current_balance', $transaction->nominal);
                } else {
                    $account->decrement('current_balance', $transaction->nominal);
                }
            }

            $label = $transaction->type === Transaction::TYPE_IN ? 'Uang Masuk' : 'Uang Keluar';
            $statusLabel = $transaction->payment_status === Transaction::STATUS_PAID ? 'Lunas' : 'Pending';

            return redirect()->route('transactions.index')->with('success', "Catatan {$label} senilai Rp " . number_format($transaction->nominal, 0, ',', '.') . " ({$statusLabel}) berhasil disimpan.");
        });
    }

    /**
     * Mark pending transaction as Paid / Cair (Settlement / Pelunasan).
     */
    public function markAsPaid(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->payment_status === Transaction::STATUS_PAID) {
            return back()->with('error', 'Transaksi ini sudah berstatus Lunas.');
        }

        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'settlement_date' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($transaction, $validated) {
            $account = Account::findOrFail($validated['account_id']);

            $transaction->update([
                'payment_status' => Transaction::STATUS_PAID,
                'account_id' => $account->id,
                'transaction_date' => $validated['settlement_date'],
            ]);

            // Adjust account balance
            if ($transaction->type === Transaction::TYPE_IN) {
                $account->increment('current_balance', $transaction->nominal);
            } else {
                $account->decrement('current_balance', $transaction->nominal);
            }
        });

        $accountName = Account::find($validated['account_id'])->name;

        return back()->with('success', "Transaksi berhasil ditandai Lunas ke rekening '{$accountName}'. Saldo telah otomatis diperbarui.");
    }

    /**
     * Delete transaction and recalculate balance if needed.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction) {
            $accountId = $transaction->account_id;
            $wasPaid = $transaction->payment_status === Transaction::STATUS_PAID;
            $nominal = $transaction->nominal;
            $type = $transaction->type;

            $transaction->delete();

            if ($wasPaid && $accountId) {
                $account = Account::find($accountId);
                if ($account) {
                    if ($type === Transaction::TYPE_IN) {
                        $account->decrement('current_balance', $nominal);
                    } else {
                        $account->increment('current_balance', $nominal);
                    }
                }
            }
        });

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }
}
