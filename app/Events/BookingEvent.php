<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BookingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;   
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data, $user)
    {
        $this->data = $data;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('booking' . '.' . $this->user->id);
    }

    public function broadcastAs()
    {
        return 'booking';
    }

    public function broadcastWith()
    {
        $userId = $this->user->id;
        $message = "Booking success!";
        $data = $this->data;

        DB::table('notifications')->insert([
            'userId' => $userId,
            'title' => 'New booking',
            'type' => 'booking',
            'image' => $this->user->avatar,
            'message' => $message,
            'data' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return [
            'name' => $this->user->fullName,
            'title' => 'New booking',
            'type' => 'booking',
            'message' => $message,
            'avatar' => $this->user->avatar,
            'data' => $this->data,
        ];
    }
}
