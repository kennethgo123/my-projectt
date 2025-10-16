<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_case_id',
        'client_id',
        'lawyer_id',
        'invoice_number',
        'title',
        'description',
        'subtotal',
        'tax',
        'discount',
        'total',
        'issue_date',
        'due_date',
        'status',
        'payment_plan',
        'installments_paid',
        'notes',
        'paymongo_payment_intent_id',
        'paymongo_source_id',
        'paymongo_payment_id',
        'payment_link',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';
    
    /**
     * Payment plan constants
     */
    const PAYMENT_PLAN_FULL = 'full';
    const PAYMENT_PLAN_3_MONTHS = '3_months';
    const PAYMENT_PLAN_6_MONTHS = '6_months';
    const PAYMENT_PLAN_1_YEAR = '1_year';

    /**
     * Get the legal case associated with the invoice.
     */
    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'legal_case_id');
    }

    /**
     * Get the client associated with the invoice.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the lawyer associated with the invoice.
     */
    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    /**
     * Get the invoice items for the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the payments for the invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_PAID 
            && $this->status !== self::STATUS_CANCELLED 
            && $this->due_date->isPast();
    }

    /**
     * Calculate and update the invoice totals
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('amount');
        $this->subtotal = $subtotal;
        $this->tax = 0;
        $this->total = $subtotal - $this->discount;
        $this->save();
    }

    public function getTotalInstallments(): int
    {
        switch ($this->payment_plan) {
            case self::PAYMENT_PLAN_3_MONTHS:
                return 3;
            case self::PAYMENT_PLAN_6_MONTHS:
                return 6;
            case self::PAYMENT_PLAN_1_YEAR:
                return 12;
            default:
                return 1;
        }
    }

    public function getInstallmentAmount(): float
    {
        $totalInstallments = $this->getTotalInstallments();
        if ($totalInstallments > 1) {
            return round($this->total / $totalInstallments, 2);
        }
        return $this->total;
    }

    /**
     * Update invoice status based on payments
     */
    public function updateStatus(): void
    {
        $paidAmount = $this->payments()->where('status', Payment::STATUS_SUCCESS)->sum('amount');
        $totalInstallments = $this->getTotalInstallments();

        if ($totalInstallments > 1) {
            // Calculate installments paid based on the sum of payments and installment amount
            // This handles cases where a client might pay more than one installment at a time or a different amount
            $installmentAmount = $this->getInstallmentAmount();
            if ($installmentAmount > 0) {
                 // Recalculate installments_paid based on total paid amount
                $this->installments_paid = floor($paidAmount / $installmentAmount);
            } else {
                // Avoid division by zero if total is 0, assume all paid if amount is also 0
                $this->installments_paid = ($this->total == 0 && $paidAmount == 0) ? $totalInstallments : 0;
            }

            if ($this->installments_paid >= $totalInstallments && $paidAmount >= $this->total) {
                $this->status = self::STATUS_PAID;
                $this->installments_paid = $totalInstallments; // Cap at total installments
            } elseif ($paidAmount > 0) {
                $this->status = self::STATUS_PARTIAL;
            } elseif ($this->due_date->isPast() && $this->status === self::STATUS_PENDING) {
                $this->status = self::STATUS_OVERDUE;
            } elseif ($this->status !== self::STATUS_DRAFT && $this->status !== self::STATUS_CANCELLED) {
                $this->status = self::STATUS_PENDING;
            }
        } else {
            // Non-installment plan
            if ($paidAmount >= $this->total) {
                $this->status = self::STATUS_PAID;
            } elseif ($paidAmount > 0) {
                $this->status = self::STATUS_PARTIAL; // Should not happen if not installment, but good to have
            } elseif ($this->due_date->isPast() && $this->status === self::STATUS_PENDING) {
                $this->status = self::STATUS_OVERDUE;
            } elseif ($this->status !== self::STATUS_DRAFT && $this->status !== self::STATUS_CANCELLED) {
                $this->status = self::STATUS_PENDING;
            }
            $this->installments_paid = ($this->status === self::STATUS_PAID) ? 1 : 0;
        }
        
        $this->save();
    }
}
