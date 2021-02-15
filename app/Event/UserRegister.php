<?php

namespace App\Event;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserRegister
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    protected $user;
    protected $type;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $type)
    {
        $this->user = $user;
        $this->type = $type;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getType()
    {
        return $this->type;
    }


}
