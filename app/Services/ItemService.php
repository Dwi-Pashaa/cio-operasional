<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemService
{
    /**
     * Get all active items with category for dropdown select.
     */
    public function getAllActive(): Collection
    {
        return Item::with('category')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get paginated items with filters.
     */
    public function getPaginated(?string $search = null, ?int $categoryId = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Item::with('category');

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

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
     * Store new item.
     */
    public function create(array $data): Item
    {
        if (empty($data['code'])) {
            $data['code'] = $this->generateUniqueCode($data['name']);
        }

        return Item::create($data);
    }

    /**
     * Update item.
     */
    public function update(Item $item, array $data): bool
    {
        return $item->update($data);
    }

    /**
     * Delete item.
     */
    public function delete(Item $item): bool
    {
        return $item->delete();
    }

    /**
     * Generate unique SKU/Code.
     */
    protected function generateUniqueCode(string $name): string
    {
        $words = explode(' ', strtoupper(preg_replace('/[^a-zA-Z0-9\s]/', '', $name)));
        $prefix = 'ITM-';
        foreach ($words as $word) {
            if (!empty($word)) {
                $prefix .= substr($word, 0, 3) . '-';
            }
        }
        $prefix = rtrim($prefix, '-');
        
        $code = substr($prefix, 0, 15);
        $counter = 1;
        while (Item::where('code', $code)->exists()) {
            $code = substr($prefix, 0, 12) . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }
}
