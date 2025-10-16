<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'type',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Type constants
     */
    const TYPE_SERVICE = 'service';
    const TYPE_EXPENSE = 'expense';
    const TYPE_BILLABLE_HOURS = 'billable_hours';
    const TYPE_OTHER = 'other';

    /**
     * Get the invoice that owns the invoice item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Calculate the amount before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoiceItem) {
            // If quantity is null, just use the unit price as the amount
            if ($invoiceItem->quantity === null) {
                $invoiceItem->amount = $invoiceItem->unit_price;
            } else {
                $invoiceItem->amount = $invoiceItem->quantity * $invoiceItem->unit_price;
            }
        });

        static::updating(function ($invoiceItem) {
            // If quantity is null, just use the unit price as the amount
            if ($invoiceItem->quantity === null) {
                $invoiceItem->amount = $invoiceItem->unit_price;
            } else {
                $invoiceItem->amount = $invoiceItem->quantity * $invoiceItem->unit_price;
            }
        });

        static::saved(function ($invoiceItem) {
            // Recalculate invoice totals when an item is saved
            if ($invoiceItem->invoice) {
                $invoiceItem->invoice->recalculateTotals();
            }
        });

        static::deleted(function ($invoiceItem) {
            // Recalculate invoice totals when an item is deleted
            if ($invoiceItem->invoice) {
                $invoiceItem->invoice->recalculateTotals();
            }
        });
    }
}
