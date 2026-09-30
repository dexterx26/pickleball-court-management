<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venue extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(VenueSetting::class, 'venue_id');
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class, 'venue_id');
    }

    public function activeCourts(): HasMany
    {
        return $this->hasMany(Court::class, 'venue_id')->where('is_active', true);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'venue_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(FacilityPhoto::class, 'venue_id')->orderBy('sort_order');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'venue_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CourtSubscription::class, 'venue_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'approved');
    }

    /**
     * Proximity sorting using Haversine formula (km)
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, float $maxKm = 100): Builder
    {
        $haversine = "(6371 * acos(
            cos(radians(?)) * cos(radians(latitude))
            * cos(radians(longitude) - radians(?))
            + sin(radians(?)) * sin(radians(latitude))
        ))";

        return $query
            ->selectRaw("venues.*, {$haversine} AS distance", [$lat, $lng, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $maxKm])
            ->orderBy('distance');
    }

    public function getPriceRangeLabel(): string
    {
        $min = $this->courts()->min('price_per_hour');
        $max = $this->courts()->max('price_per_hour');

        if (!$min && !$max) {
            return '₱150 / hr';
        }

        if ($min == $max) {
            return '₱' . number_format($min, 0) . ' / hr';
        }

        return '₱' . number_format($min, 0) . ' - ₱' . number_format($max, 0) . ' / hr';
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }
        if (str_starts_with($this->logo_path, 'http')) {
            return $this->logo_path;
        }
        return asset('storage/' . ltrim($this->logo_path, '/'));
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image_path) {
            return null;
        }
        if (str_starts_with($this->cover_image_path, 'http')) {
            return $this->cover_image_path;
        }
        return asset('storage/' . ltrim($this->cover_image_path, '/'));
    }
}
