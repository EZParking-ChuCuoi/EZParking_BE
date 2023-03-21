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

class TimeOutBookingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $message;
    public $owner;
    public $data;
 
 


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user,$parkingInfo,$time,$owner)
    {
        $this->user = $user;
        $this->owner = $owner;
        $this->data = $parkingInfo;
        $this->message = " {$time}  minutes left until you finish parking  {$parkingInfo->parkingName}";
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('time-out' . '.' . $this->user->id);
    }

    public function broadcastAs()
    {
        return 'time-out';
    }

    public function broadcastWith()
    {
         
          
        $userId = $this->user->id;
        $message = $this->message;

        DB::table('notifications')->insert([
            'nameUserSend' => $this->owner->fullName,
            'userId' => $userId,
            'title' => 'Time Out Booking',
            'type' => 'timeOutBooking',
            'image' => $this->owner->avatar,
            'message' => $message,
            'data' => json_encode($this->data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return [
            'name' => $this->owner->fullName,
            'title' => 'Time Out Booking',
            'type' => 'timeOutBooking',
            'message' => $message,
            'avatar'=>$this->owner->avatar,  
            'data' => $this->data,
        ];
    }
}
