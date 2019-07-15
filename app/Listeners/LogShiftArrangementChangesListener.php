<?php

namespace App\Listeners;

use App\Events\ShiftArrangementChangeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\ShiftArrangementChange;

class LogShiftArrangementChangesListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ShiftArrangementChangeEvent  $event
     * @return void
     */
    public function handle(ShiftArrangementChangeEvent $event)
    {
        ShiftArrangementChange::create([
            'date' => $event->date,
            'shift_id' => $event->shift->id,
            'on_duty_staff_id' => $event->on_duty_staff->id,
            'changer_id' => $event->changer->id,
            'is_locked' => $event->is_locked,
            'is_up' => $event->is_up,
        ]);
    }
}
