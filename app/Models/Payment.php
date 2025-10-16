<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'client_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'payment_date',
        'receipt_path',
        'payment_notes',
        'payment_details',
        'paymongo_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'payment_details' => 'array',
    ];

    /**
     * Payment method constants
     */
    const METHOD_GCASH = 'gcash';
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_DEBIT_CARD = 'debit_card';
    const METHOD_CASH = 'cash';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_PRO_BONO = 'pro_bono';
    const METHOD_OTHER = 'other';

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the invoice associated with the payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the client associated with the payment.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Update invoice status when payment is saved or updated
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($payment) {
            // Update invoice status when payment is saved
            if ($payment->invoice) {
                $payment->invoice->updateStatus();
            }
        });

        static::deleted(function ($payment) {
            // Update invoice status when payment is deleted
            if ($payment->invoice) {
                $payment->invoice->updateStatus();
            }
        });
    }
}
