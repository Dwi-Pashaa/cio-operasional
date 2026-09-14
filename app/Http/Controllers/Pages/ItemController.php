<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\ExpenseCategoryService;
use App\Services\ItemService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    protected ItemService $itemService;
    protected ExpenseCategoryService $categoryService;

    public function __construct(ItemService $itemService, ExpenseCategoryService $categoryService)
    {
        $this->itemService = $itemService;
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $this->authorize('lihat barang');

        $search = $request->get('search');
        $categoryId = $request->get('category_id');

        $items = $this->itemService->getPaginated($search, $categoryId ? (int)$categoryId : null, 10);
        $categories = $this->categoryService->getAllActive();

        return view('pages.item.index', compact('items', 'categories', 'search', 'categoryId'));
    }

    public function create()
    {
        $this->authorize('buat barang');

        $categories = $this->categoryService->getAllActive();

        return view('pages.item.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('buat barang');

        $validated = $request->validate([
            'category_id'   => 'nullable|exists:expense_categories,id',
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50|unique:items,code',
            'unit'          => 'required|string|max:50',
            'default_price' => 'nullable|numeric|min:0',
            'description'   => 'nullable|string',
            'is_active'     => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['default_price'] = (float) ($validated['default_price'] ?? 0);

        $item = $this->itemService->create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Barang berhasil ditambahkan ke katalog.',
                'data'    => $item,
            ]);
        }

        return redirect()->route('item.index')->with('success', 'Barang berhasil ditambahkan ke katalog.');
    }

    public function show($id)
    {
        $this->authorize('lihat barang');

        $item = Item::with('category')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $item,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('ubah barang');

        $item = Item::findOrFail($id);
        $categories = $this->categoryService->getAllActive();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => $item,
                'categories' => $categories,
            ]);
        }

        return view('pages.item.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ubah barang');

        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'category_id'   => 'nullable|exists:expense_categories,id',
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50|unique:items,code,' . $item->id,
            'unit'          => 'required|string|max:50',
            'default_price' => 'nullable|numeric|min:0',
            'description'   => 'nullable|string',
            'is_active'     => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['default_price'] = (float) ($validated['default_price'] ?? 0);

        $this->itemService->update($item, $validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data barang berhasil diperbarui.',
                'data'    => $item,
            ]);
        }

        return redirect()->route('item.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('hapus barang');

        $item = Item::findOrFail($id);
        $this->itemService->delete($item);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Barang berhasil dihapus dari katalog.',
            ]);
        }

        return redirect()->route('item.index')->with('success', 'Barang berhasil dihapus dari katalog.');
    }
}
