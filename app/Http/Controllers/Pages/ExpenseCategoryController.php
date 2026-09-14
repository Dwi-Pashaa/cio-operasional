<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    protected ExpenseCategoryService $categoryService;

    public function __construct(ExpenseCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $this->authorize('lihat kategori');

        $search = $request->get('search');
        $categories = $this->categoryService->getPaginated($search, 10);

        return view('pages.expense-category.index', compact('categories', 'search'));
    }

    public function create()
    {
        $this->authorize('buat kategori');

        return view('pages.expense-category.create');
    }

    public function store(Request $request)
    {
        $this->authorize('buat kategori');

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:expense_categories,code',
            'description' => 'nullable|string',
            'is_active'   => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $category = $this->categoryService->create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori pengeluaran berhasil ditambahkan.',
                'data'    => $category,
            ]);
        }

        return redirect()->route('expense-category.index')->with('success', 'Kategori pengeluaran berhasil ditambahkan.');
    }

    public function show($id)
    {
        $this->authorize('lihat kategori');

        $category = ExpenseCategory::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $category,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('ubah kategori');

        $category = ExpenseCategory::findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => $category,
            ]);
        }

        return view('pages.expense-category.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ubah kategori');

        $category = ExpenseCategory::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:expense_categories,code,' . $category->id,
            'description' => 'nullable|string',
            'is_active'   => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $this->categoryService->update($category, $validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori pengeluaran berhasil diperbarui.',
                'data'    => $category,
            ]);
        }

        return redirect()->route('expense-category.index')->with('success', 'Kategori pengeluaran berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('hapus kategori');

        $category = ExpenseCategory::findOrFail($id);
        $this->categoryService->delete($category);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori pengeluaran berhasil dihapus.',
            ]);
        }

        return redirect()->route('expense-category.index')->with('success', 'Kategori pengeluaran berhasil dihapus.');
    }
}
