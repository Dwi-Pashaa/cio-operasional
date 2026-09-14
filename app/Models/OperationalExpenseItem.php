<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalExpenseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'item_id',
        'item_name',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity'   => 'float',
        'unit_price' => 'float',
        'subtotal'   => 'float',
    ];

    public function expense()
    {
        return $this->belongsTo(OperationalExpense::class, 'expense_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
