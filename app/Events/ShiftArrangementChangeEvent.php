<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ShiftArrangementChangeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $date;
    public $shift;
    public $on_duty_staff;
    public $is_locked;
    public $is_up;
    public $changer;

    /**
     * Create a new event instance.
     *
     * @param Illuminate\Support\Carbon $date
     * @param App\Shift $shift
     * @param App\User $on_duty_staff
     * @param boolean $is_locked
     * @param App\User $changer
     * @return void
     */
    public function __construct($date, $shift, $on_duty_staff, $is_locked, $is_up, $changer)
    {
        $this->date = $date;
        $this->shift = $shift;
        $this->on_duty_staff = $on_duty_staff;
        $this->is_locked = $is_locked;
        $this->is_up = $is_up;
        $this->changer = $changer;
    }

    // /**
    //  * Get the channels the event should broadcast on.
    //  *
    //  * @return \Illuminate\Broadcasting\Channel|array
    //  */
    // public function broadcastOn()
    // {
    //     return new PrivateChannel('channel-name');
    // }
}
