<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'xendit_simulation_mode' => 'boolean',
        'paymongo_simulation_mode' => 'boolean',
        'holding_duration_seconds' => 'integer',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get or create settings for a specific venue
     */
    public static function forVenue(int $venueId): self
    {
        $venue = Venue::find($venueId);
        $venueName = $venue ? $venue->name : 'Paddle Field Sports Center';

        return static::firstOrCreate(['venue_id' => $venueId], [
            'venue_name' => $venueName,
            'tagline' => 'Premier Indoor & Outdoor Pickleball Facility',
            'description' => 'Equipped with tournament-grade cushioned acrylic courts, high-powered LED lighting, and premium club amenities.',
            'phone' => '+63 917 555 7233',
            'email' => 'contact@paddlefield.com',
            'address' => 'Bacal 3, Talavera, Nueva Ecija',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'xendit_simulation_mode' => true,
            'paymongo_simulation_mode' => true,
            'manual_bank_name' => 'GCash / Maya / BDO Bank',
            'manual_account_name' => $venueName,
            'manual_account_number' => '0917-555-7233',
            'manual_payment_instructions' => "1. Transfer the exact reservation amount via GCash, Maya, or BDO.\n2. Write your Booking Reference code in the transfer notes.\n3. Take a screenshot of the completed transaction receipt.\n4. Upload the screenshot below to secure your court reservation.",
            'holding_duration_seconds' => 120,
        ]);
    }

    /**
     * Backward-compatible default settings accessor
     */
    public static function getSettings(): self
    {
        return static::forVenue(1);
    }
}
