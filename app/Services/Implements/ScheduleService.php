<?php

namespace App\Services\Implements;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    public function setScheduleExpiredTime()
    {



        // Define the time max
        define('MAX_BOOKING_HOURS', 20);
        $now = Carbon::now();
        // Calculate the end time for the time frame
        $end_time = $now->copy()->addHours(MAX_BOOKING_HOURS);

        // query for get booking out time
        $bookings = Booking::whereBetween('returnDate', [$now, $end_time])
            ->get();
        // return $bookings;
        foreach ($bookings as $booking) {
            // Calculate the time difference between the current time and the returnDateTime for the booking
            $time_diff = $now->diffInHours($booking->returnDate);
            echo $time_diff . "</br>";
            // Check if the booking is almost time out
            if ($time_diff <= MAX_BOOKING_HOURS) {
                // Notify  
                // echo $booking;
                Log::debug($booking);
            }
        }

    }
}
