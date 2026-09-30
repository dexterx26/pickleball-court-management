<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'subscription_price_monthly' => 'decimal:2',
        'subscription_price_annual' => 'decimal:2',
        'trial_days' => 'integer',
        'grace_period_days' => 'integer',
        'auto_suspend_on_expiry' => 'boolean',
    ];

    public static function get(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'subscription_price_monthly' => 300.00,
            'subscription_price_annual' => 3000.00,
            'trial_days' => 14,
            'grace_period_days' => 3,
            'auto_suspend_on_expiry' => true,
            'platform_payment_mode' => 'paymongo',
            'platform_bank_name' => 'BDO / GCash Official',
            'platform_bank_account_name' => 'Pickleball Management Platform',
            'platform_bank_account_number' => '0917-000-PLATFORM',
        ]);
    }

    public static function getSettings(): self
    {
        return static::get();
    }

    public function getMonthlyPricePerCourtAttribute(): float
    {
        return (float) ($this->subscription_price_monthly ?? 300.00);
    }

    public function setMonthlyPricePerCourtAttribute($value): void
    {
        $this->attributes['subscription_price_monthly'] = $value;
    }

    public function getAnnualPricePerCourtAttribute(): float
    {
        return (float) ($this->subscription_price_annual ?? 3000.00);
    }

    public function setAnnualPricePerCourtAttribute($value): void
    {
        $this->attributes['subscription_price_annual'] = $value;
    }

    public function getTrialPeriodDaysAttribute(): int
    {
        return (int) ($this->trial_days ?? 14);
    }

    public function setTrialPeriodDaysAttribute($value): void
    {
        $this->attributes['trial_days'] = $value;
    }

    public static function subscriptionPriceMonthly(): float
    {
        return (float) static::get()->subscription_price_monthly;
    }

    public static function subscriptionPriceAnnual(): float
    {
        return (float) static::get()->subscription_price_annual;
    }

    public static function trialDays(): int
    {
        return (int) static::get()->trial_days;
    }

    public static function gracePeriodDays(): int
    {
        return (int) static::get()->grace_period_days;
    }
}
