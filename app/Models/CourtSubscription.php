<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourtSubscription extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function getPlanAttribute(): string
    {
        return $this->billing_cycle ?? 'monthly';
    }

    public function setPlanAttribute($value): void
    {
        $this->attributes['billing_cycle'] = $value;
    }

    public function getPriceAttribute(): float
    {
        return (float) ($this->price_snapshot ?? 300.00);
    }

    public function setPriceAttribute($value): void
    {
        $this->attributes['price_snapshot'] = $value;
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class)->withTrashed();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class, 'subscription_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'active')
                ->where(function ($q2) {
                    $q2->whereNull('ends_at')->orWhere('ends_at', '>', now());
                });
        })->orWhere(function ($q) {
            $q->where('status', 'trial')
                ->where('trial_ends_at', '>', now());
        });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('status', 'active')->whereNotNull('ends_at')->where('ends_at', '<=', now());
            });
    }

    public function isInTrial(): bool
    {
        $trialEnd = $this->trial_ends_at ?? $this->ends_at;
        return $this->status === 'trial' && $trialEnd && now()->lt($trialEnd);
    }

    public function isActive(): bool
    {
        if ($this->isInTrial()) {
            return true;
        }

        return $this->status === 'active' && (!$this->ends_at || now()->lt($this->ends_at));
    }

    public function isInGracePeriod(): bool
    {
        if ($this->isActive()) {
            return false;
        }

        $graceDays = PlatformSetting::gracePeriodDays();
        $expiryDate = $this->ends_at ?? $this->trial_ends_at;

        if (!$expiryDate) {
            return false;
        }

        return now()->gt($expiryDate) && now()->lt($expiryDate->copy()->addDays($graceDays));
    }

    public function daysUntilExpiry(): int
    {
        $target = ($this->isInTrial() ? ($this->trial_ends_at ?? $this->ends_at) : $this->ends_at) ?? $this->trial_ends_at;
        if (!$target) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($target, false));
    }
}
