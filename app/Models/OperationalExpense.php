<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'user_id',
        'category_id',
        'title',
        'vendor_name',
        'transaction_date',
        'subtotal_amount',
        'has_admin_fee',
        'admin_fee',
        'grand_total',
        'payment_channel',
        'bank_code',
        'bank_name',
        'account_number',
        'account_holder_name',
        'finance_reference_id',
        'finance_response',
        'status',
        'notes',
        'attachment_receipt',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'has_admin_fee'    => 'boolean',
        'subtotal_amount'  => 'float',
        'admin_fee'        => 'float',
        'grand_total'      => 'float',
        'finance_response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(OperationalExpenseItem::class, 'expense_id');
    }

    public function getXenditDisbursementIdAttribute(): ?string
    {
        return $this->finance_response['xendit_disbursement']['id'] ?? null;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->payment_channel === 'xendit') {
            return match ($this->status) {
                'pending'   => 'Sedang Diproses Xendit',
                'success'   => 'Transfer Berhasil',
                'failed'    => 'Transfer Gagal',
                'refunded'  => 'Dana Direfund',
                default     => ucfirst($this->status),
            };
        }

        return match ($this->status) {
            'success'   => 'Kas Selesai',
            'failed'    => 'Gagal',
            'refunded'  => 'Direfund',
            default     => ucfirst($this->status),
        };
    }
}
