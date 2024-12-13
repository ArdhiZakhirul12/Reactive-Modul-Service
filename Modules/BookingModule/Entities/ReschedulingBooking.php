<?php

namespace Modules\BookingModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\UserManagement\Entities\Serviceman;

class ReschedulingBooking extends Model
{
    use HasFactory;

    protected $casts = [
        'evidence_photos' => 'array',
        'ongoing_photos' => 'array',
    ];
    protected $fillable = ['booking_id','serviceman_id','ongoing_photos', 'evidence_photos', 'serviceman_note'];

    protected static function newFactory()
    {
        return \Modules\BookingModule\Database\factories\ReschedulingBookingFactory::new();
    }

    /**
     * Get the booking that owns the ReschedulingBooking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    /**
     * Get the serviceman that owns the ReschedulingBooking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function serviceman(): BelongsTo
    {
        return $this->belongsTo(Serviceman::class, 'serviceman_id', 'id');
    }
}
