<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display categories list.
     */
    public function index(): View
    {
        $incomeCategories = Category::income()->withCount('transactions')->orderBy('name')->get();
        $expenseCategories = Category::expense()->withCount('transactions')->orderBy('name')->get();

        return view('categories.index', [
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * Show create category form.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Store new category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:in,out'],
        ]);

        $category = Category::create($validated);

        return redirect()->route('categories.index')->with('success', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    /**
     * Show edit category form.
     */
    public function edit(Category $category): View
    {
        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:in,out'],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    /**
     * Delete category if no transactions attached.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->transactions()->exists()) {
            return back()->with('error', "Kategori '{$category->name}' tidak dapat dihapus karena telah digunakan dalam transaksi.");
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', "Kategori berhasil dihapus.");
    }
}
