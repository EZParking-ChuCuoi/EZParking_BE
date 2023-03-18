<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WishlistEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userName;
    public $message;

    public $ownerId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userName, $ownerId,$nameParkingLot)
    {
        $this->userName = $userName;
        $this->message = "{$userName} add wishlist parkingLot {$nameParkingLot}";
        $this->ownerId = $ownerId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('wishlists' . '.' . $this->ownerId);
    }

    public function broadcastAs()
    {
        return 'wishlist';
    }

    public function broadcastWith()
    {
        $now = Carbon::now();
        $now->modify('+2 minutes');
        return [
            'data' => $this->message,
            'time' =>'2023-03-18T13:34:34.672Z',
        ];
    }
}