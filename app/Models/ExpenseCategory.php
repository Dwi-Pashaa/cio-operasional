<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }

    public function expenses()
    {
        return $this->hasMany(OperationalExpense::class, 'category_id');
    }
}
