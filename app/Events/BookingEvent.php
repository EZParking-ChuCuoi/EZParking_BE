<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $userInfo;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data, $userInfo)
    {
        $this->message = $data;
        $this->userInfo = $userInfo;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('booking' . '.' . $this->userInfo);
    }

    public function broadcastAs()
    {
        return 'booking';
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->message,
        ];
    }
}
