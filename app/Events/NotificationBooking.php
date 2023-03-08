<?php
// app/Events/NotificationCreated.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationBooking implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $data; // Define a public property for any additional data to include in the broadcast

    public function __construct(array $data )
    {
        // Assign the Notification model and additional data to the properties
        $this->data = $data;
    }

    public function broadcastOn()
    {
        // Define the channel to broadcast on
        return new PrivateChannel('booking-notification');
    }

    public function broadcastAs()
    {
        // Define the event name to broadcast as
        return 'notification-booking';
    }

    public function broadcastWith()
    {
        // Define any data to include in the broadcast payload
        return [
            'data' => $this->data,
        ];
    }
}
