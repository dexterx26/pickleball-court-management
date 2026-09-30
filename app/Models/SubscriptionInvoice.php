<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CourtSubscription::class, 'subscription_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class)->withTrashed();
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }
        if (str_starts_with($this->receipt_path, 'http')) {
            return $this->receipt_path;
        }
        return asset('storage/' . ltrim($this->receipt_path, '/'));
    }

    public function markAsPaid(string $method, ?string $checkoutId = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_method' => $method,
            'paymongo_checkout_id' => $checkoutId ?? $this->paymongo_checkout_id,
            'paid_at' => now(),
        ]);

        if ($this->subscription) {
            $durationMonths = $this->billing_cycle === 'annual' ? 12 : 1;
            $newEndsAt = ($this->subscription->ends_at && $this->subscription->ends_at->isFuture())
                ? $this->subscription->ends_at->addMonths($durationMonths)
                : now()->addMonths($durationMonths);

            $this->subscription->update([
                'status' => 'active',
                'starts_at' => $this->subscription->starts_at ?? now(),
                'ends_at' => $newEndsAt,
            ]);
        }
    }

    public static function generateNextInvoiceNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('INV-%s-%04d', $year, $count);
    }
}
