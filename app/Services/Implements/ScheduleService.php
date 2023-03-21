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
        define('MAX_BOOKING_MiNUTE', 20);
        $now = Carbon::now();
        // Calculate the end time for the time frame
        $end_time = $now->copy()->addMinutes(MAX_BOOKING_MiNUTE);

        Log::debug('------------Now-------------'.$now);
        Log::debug($end_time);
        // query for get booking out time
        $bookings = Booking::whereBetween('returnDate', [$now, $end_time])
            ->groupBy('returnDate', 'bookDate','userId')
            ->select('returnDate', 'bookDate','userId')
            ->distinct()
            ->get();
      
      
        foreach ($bookings as $booking) {
            // Calculate the time difference between the current time and the returnDateTime for the booking
            $time_diff = $now->diffInMinutes($booking->returnDate);
            echo $time_diff . "</br>";
            // Check if the booking is almost time out
            if ($time_diff <= MAX_BOOKING_MiNUTE) {
                // Notify  
                // echo $booking;
                Log::debug($booking);
            }
        }
    }
}
