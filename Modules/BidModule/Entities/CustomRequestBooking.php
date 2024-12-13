<?php

namespace Modules\BidModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookingModule\Entities\Booking;

class CustomRequestBooking extends Model
{
    use HasFactory;

    protected $fillable = ['custom_request_id', 'booking_id'];

    /**
     * Get the booking that owns the CustomRequestBooking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    /**
     * Get the customRequest that owns the CustomRequestBooking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customRequest(): BelongsTo
    {
        return $this->belongsTo(CustomRequest::class, 'custom_request_id', 'id');
    }

    protected static function newFactory()
    {
        return \Modules\BidModule\Database\factories\CustomRequestBookingFactory::new();
    }
}
