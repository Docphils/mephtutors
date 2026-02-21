<?php

namespace App\Observers;

use App\Models\Booking;

class BookingObserver
{

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // Update status_changed_at when the status changes to 'Completed'
        if ($booking->isDirty('status')) {
            // if booking moved to completed, set completed timestamps and update tutor request
            if (strtolower($booking->status) === 'completed' || $booking->status === 'Completed') {
                $booking->update(['completed_at' => now()]);
                $tutorRequest = $booking->tutorRequest;
                if ($tutorRequest) {
                    $tutorRequest->status = 'completed';
                    $tutorRequest->completed_at = now();
                    $tutorRequest->save();
                }
            }
        }
    }

}
