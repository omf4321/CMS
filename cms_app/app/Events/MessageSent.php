<?php

namespace App\Events;

// use App\Client;
use App\Model\Admin\daily_schedule;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * User that sent the message
     *
     * @var User
     */
    // public $user;

    /**
     * Message details
     *
     * @var Message
     */
    public $schedule;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($schedule)
    {
        // $this->user = $user;
        $this->schedule = $schedule;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    
    public function broadcastAs()
    {
        // return 'MessageSent' . $this->schedule[0]->batch_id;
        return 'MessageSent';
    }

    public function broadcastOn()
    {
        return new Channel('chat');
        // return new PrivateChannel('chat');
    }
}