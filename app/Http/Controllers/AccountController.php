<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display a listing of bank accounts and cash wallets.
     */
    public function index(): View
    {
        $accounts = Account::withCount('transactions')->orderBy('name')->get();
        $totalCurrentBalance = $accounts->sum('current_balance');
        $totalInitialBalance = $accounts->sum('initial_balance');

        return view('accounts.index', [
            'accounts' => $accounts,
            'totalCurrentBalance' => $totalCurrentBalance,
            'totalInitialBalance' => $totalInitialBalance,
        ]);
    }

    /**
     * Show the form for creating a new account.
     */
    public function create(): View
    {
        return view('accounts.create');
    }

    /**
     * Store a newly created account in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:accounts,name'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
        ]);

        $account = Account::create([
            'name' => $validated['name'],
            'initial_balance' => $validated['initial_balance'],
            'current_balance' => $validated['initial_balance'], // Initial current balance matches initial balance
        ]);

        return redirect()->route('accounts.index')->with('success', "Sumber kas '{$account->name}' berhasil ditambahkan.");
    }

    /**
     * Show the form for editing the specified account.
     */
    public function edit(Account $account): View
    {
        return view('accounts.edit', [
            'account' => $account,
        ]);
    }

    /**
     * Update the specified account in storage.
     */
    public function update(Request $request, Account $account): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:accounts,name,' . $account->id],
        ]);

        $account->update(['name' => $validated['name']]);

        return redirect()->route('accounts.index')->with('success', "Sumber kas '{$account->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified account from storage if no transactions are attached.
     */
    public function destroy(Account $account): RedirectResponse
    {
        if ($account->transactions()->exists()) {
            return back()->with('error', "Sumber kas '{$account->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi.");
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('success', "Sumber kas berhasil dihapus.");
    }

    /**
     * Recalculate balance for audit trail.
     */
    public function recalculate(Account $account): RedirectResponse
    {
        $account->recalculateBalance();

        return back()->with('success', "Saldo riil '{$account->name}' berhasil disinkronisasi ulang.");
    }
}
