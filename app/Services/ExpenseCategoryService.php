<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseCategoryService
{
    /**
     * Get all active categories.
     */
    public function getAllActive(): Collection
    {
        return ExpenseCategory::where('is_active', true)->orderBy('name', 'asc')->get();
    }

    /**
     * Get paginated categories with search.
     */
    public function getPaginated(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = ExpenseCategory::withCount(['items', 'expenses']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name', 'asc')->paginate($perPage)->withQueryString();
    }

    /**
     * Store new category.
     */
    public function create(array $data): ExpenseCategory
    {
        if (empty($data['code'])) {
            $data['code'] = $this->generateUniqueCode($data['name']);
        }

        return ExpenseCategory::create($data);
    }

    /**
     * Update category.
     */
    public function update(ExpenseCategory $category, array $data): bool
    {
        return $category->update($data);
    }

    /**
     * Delete category.
     */
    public function delete(ExpenseCategory $category): bool
    {
        return $category->delete();
    }

    /**
     * Generate code from name.
     */
    protected function generateUniqueCode(string $name): string
    {
        $words = explode(' ', strtoupper(preg_replace('/[^a-zA-Z0-9\s]/', '', $name)));
        $prefix = 'CAT-';
        foreach ($words as $word) {
            if (!empty($word)) {
                $prefix .= substr($word, 0, 3) . '-';
            }
        }
        $prefix = rtrim($prefix, '-');
        
        $code = substr($prefix, 0, 15);
        $counter = 1;
        while (ExpenseCategory::where('code', $code)->exists()) {
            $code = substr($prefix, 0, 12) . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }
}
