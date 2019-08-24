<?php

namespace App\Http\Controllers;

use App\Area;
use App\Shift;
use App\ShiftArrangementLock;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
// use Illuminate\Support\Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

class ShiftArrangementLocksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['update']]);
    }

    private static $DEFAULT_DURATION_DAYS = 30;
    private static $START_OF_WEEK = Carbon::SUNDAY;
    private static $END_OF_WEEK = Carbon::SATURDAY;

    static function getIsLocked($from_date, $to_date, $shift = null, $area = null)
    {
        $shifts = [];

        $query = ShiftArrangementLock::with([
            'shift' => function ($query) { $query->withTrashed(); },
        ])->whereBetween('date', [$from_date, $to_date]);

        if ($shift)
        {
            $shifts = Shift::where('uuid', $shift)->withTrashed()->get(); // could be used by foreach syntax

            $query = $query->where('shift_id', function ($query) use ($shift) {
                $query->select('id')->from((new Shift())->getTable())
                    ->where('uuid', $shift);
            });
        }
        else if ($area)
        {
            $shifts = Shift::where('area_id', function ($query) use ($area) {
                $query->select('id')->from((new Area())->getTable())
                    ->where('uuid', $area);
            })->get();

            $query = $query->whereIn('shift_id', function($query) use ($area) {
                $query->select('id')->from((new Shift())->getTable())
                    ->where('area_id', function($query) use ($area) {
                        $query->select('id')->from((new Area())->getTable())
                            ->where('uuid', $area);
                    });
            });
        }
        else
        {
            $shifts = Shift::all();
        }

        $locks = [];
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());
        $time_now = now();

        // defaults
        foreach ($shifts as $key => $shift) foreach ($period as $date)
            $locks[$shift->uuid][$date->format('Y-m-d')] = $date <= $time_now;

        $query->get()->each(function ($item, $key) use (&$locks, $time_now) {
            if (static::isValidLock($item, $time_now))
                $locks[$item->shift->uuid][$item->date->format('Y-m-d')] = $item->is_locked;
        });

        return $locks;
    }

    static function isValidLock(?ShiftArrangementLock $lock, Carbon $time_now = null)
    {
        if ($time_now == null) $time_now = now();
        if ($lock == null) return false;
        if ($lock->date > $time_now) return true;
        if ($lock->updated_at != null && $lock->updated_at > $lock->date)
            return true;
        if ($lock->created_at != null && $lock->created_at > $lock->date)
            return true;
        return false;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => [
                'date_format:Y-m-d',
            ],
            'to_date' => [
                'date_format:Y-m-d',
            ],
            'area' => [
                'exists:areas,uuid',
            ],
            'shift' => [
                'exists:shifts,uuid',
            ],
        ]);

        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        try
        {
            $time_now = now();
            $from_date = new Carbon($request->input('from_date', $time_now));
            $to_date = new Carbon($request->input('to_date', (new Carbon($time_now))
                ->addDays(ShiftArrangementLocksController::$DEFAULT_DURATION_DAYS)));
        } catch (Exception $e)
        {
            abort(400);
        }
        $from_date = $from_date->startOfWeek(ShiftArrangementLocksController::$START_OF_WEEK);
        $to_date = $to_date->endOfWeek(ShiftArrangementLocksController::$END_OF_WEEK);
        $area = $request->input('area');
        $shift = $request->input('shift');

        return ShiftArrangementLocksController::getIsLocked($from_date, $to_date, $shift, $area);
    }

    // /**
    //  * Show the form for creating a new resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function create()
    // {
    //     abort(404);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(Request $request)
    // {
    //     abort(404);
    // }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show(Request $request)
    // {
    //     abort(404);
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit(Request $request)
    // {
    //     abort(404);
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lock' => [
                'required',
                'boolean',
            ],
            'date' => [
                'required_without_all:from_date,to_date',
                'date_format:Y-m-d',
            ],
            'from_date' => [
                'required_with:to_date',
                'date_format:Y-m-d',
            ],
            'to_date' => [
                'required_with:from_date',
                'date_format:Y-m-d',
            ],
            'area' => [
                'exists:areas,uuid'
            ],
            'shift' => [
                'exists:shifts,uuid'
            ],
            'shifts.*' => [
                'exists:shifts,uuid'
            ],
        ]);

        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        $lock = $request->input('lock');
        $date = $request->input('date');
        $area_uuid = $request->input('area');
        $shift_uuid = $request->input('shift');
        $shifts_uuids = $request->input('shifts');

        if ($date)
        {
            $from_date = $date;
            $to_date = $date;
        }
        else
        {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
        }
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        $shifts_query = Shift::with(['area_eager' => function ($query) {
            $query->withTrashed();
        }]);

        if ($shifts_uuids)
            $shifts_query = $shifts_query->withTrashed()->whereIn('uuid', $shifts_uuids);
        else if ($shift_uuid)
            $shifts_query = $shifts_query->withTrashed()->where('uuid', $shift_uuid);
        else if ($area_uuid)
            $shifts_query = $shifts_query->where('area_id', function ($query) use ($area_uuid) {
                $query->select('id')->from((new Area())->getTable())->where('uuid', $area_uuid);
            });

        $shifts = $shifts_query->get();
        $locks = array(); // [shift(UUID)][date(Y-m-d)] => bool
        $time_now = now();
        $updatable_shifts = array(); // array[Shift]

        foreach ($shifts as $shift)
        {
            // admin can update all locks' status
            // area manager can update his/her area's locks' status
            // other people cannot update any locks' status
            if (Auth::user()->is_admin || (
                    $shift->area && $shift->area->responsible_person_id == Auth::user()->id))
                array_push($updatable_shifts, $shift);

            // default lock status
            foreach ($period as $date)
                $locks[$shift->uuid][$date->format('Y-m-d')] = $date <= $time_now; // default
        }

        // update existing locks in DB
        ShiftArrangementLock::query()
            ->whereIn('shift_id', array_map(
                function (Shift $shift) { return $shift->id; }, $updatable_shifts
            ))
            ->whereBetween('date', array($from_date, $to_date))
            ->update([
                'is_locked' => $lock,
            ]);

        // query existing locks
        $lock_objects = ShiftArrangementLock::with([
                'shift' => function ($query) { $query->withTrashed(); },
            ])
            ->whereIn('shift_id', array_map(
                function (Shift $shift) { return $shift->id; }, $updatable_shifts
            ))
            ->whereBetween('date', array($from_date, $to_date))
            ->get();

        // insert non-existing and updatable locks
        $existing_locks = array(); // [shift_id][date] => bool
        foreach ($updatable_shifts as $shift)
            foreach ($period as $date)
                $existing_locks[$shift->id][$date->timestamp] = false;
        $lock_objects->each(function (ShiftArrangementLock $lock) use (&$existing_locks) {
            $existing_locks[$lock->shift->id][$lock->date->timestamp] = true;
        });
        $should_insert_locks_data = array();
        foreach ($updatable_shifts as $shift)
            foreach ($period as $date)
                if (!$existing_locks[$shift->id][$date->timestamp])
                    array_push($should_insert_locks_data, [
                        'shift_id' => $shift->id,
                        'date' => $date->format('Y-m-d'),
                        'is_locked' => $lock,
                        'updated_at' => $time_now,
                    ]);
        ShiftArrangementLock::insert($should_insert_locks_data);

        // get all locks
        $lock_objects = ShiftArrangementLock::with([
                'shift' => function ($query) { $query->withTrashed(); },
            ])
            ->whereIn('shift_id', $shifts->map(function (Shift $shift) {
                return $shift->id;
            }))
            ->whereBetween('date', array($from_date, $to_date))
            ->get();

        // update the default value to the DB records
        $lock_objects->each(function (ShiftArrangementLock $lock) use (&$locks, $time_now) {
            if (static::isValidLock($lock, $time_now))
                $locks[$lock->shift->uuid][$lock->date->format('Y-m-d')] = $lock->is_locked;
        });

        return $locks;
    }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy(Request $request)
    // {
    //     abort(404);
    // }
}
