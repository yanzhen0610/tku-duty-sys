<?php

namespace App\Http\Controllers;

use App\Area;
use App\Shift;
use App\ShiftArrangementLock;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Carbon\CarbonPeriod;
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

        $query = ShiftArrangementLock::whereBetween('date', [$from_date, $to_date]);

        if ($shift)
        {
            $shifts = Shift::where('uuid', $shift)->get(); // cound be used by foreach syntax

            $query = $query->where(
                'shift_id',
                function ($query) use ($shift)
                {
                    $query->select('id')
                        ->from((new Shift())->getTable())
                        ->where('uuid', $shift);
                }
            );
        }
        else if ($area)
        {
            Area::with('shifts')->where('uuid', $area)->get()->each(
                function ($item, $key) use (&$shifts)
                {
                    $item->shifts->each(
                        function ($item, $key) use (&$shifts)
                        {
                            array_push($shifts, $item);
                        }
                    );
                }
            );

            $query = $query->whereIn(
                'shift_id',
                function($query) use ($area)
                {
                    $query->select('id')
                        ->from((new Shift())->getTable())
                        ->where(
                            'area_id',
                            function($query) use ($area)
                            {
                                $query->select('id')
                                    ->from((new Area())->getTable())
                                    ->where('uuid', $area);
                            }
                        );
                }
            );
        }
        else
        {
            Area::with('shifts')->get()->each(
                function ($item, $key) use (&$shifts)
                {
                    $item->shifts->each(
                        function ($item, $key) use (&$shifts)
                        {
                            array_push($shifts, $item);
                        }
                    );
                }
            );
        }

        $locks = [];
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());
        $time_now = now();

        foreach ($shifts as $key => $shift)
        {
            foreach ($period as $date)
            {
                $locks[$shift->uuid][$date->format('Y-m-d')] = $date < $time_now;
            }
        }

        $query->get()->each(function ($item, $key) use (&$locks, $time_now)
        {
            if ($item->date > $time_now || $item->updated_at > $item->date)
                $locks[$item->shift->uuid][$item->date->format('Y-m-d')] = $item->is_locked;
        });

        return $locks;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
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
            $from_date = new Carbon($request->input('from_date'));
            $to_date = new Carbon($request->input('to_date', $from_date));
            $to_date->addDays(ShiftArrangementLocksController::$DEFAULT_DURATION_DAYS);
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
        return $request;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
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
        $area = $request->input('area');
        $shift = $request->input('shift');
        $shifts_list = $request->input('shifts');

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

        $shifts = [];

        if ($shifts)
        {
            $shifts = Shift::with('area')->whereIn('uuid', $shifts_list)->get();
        }
        else if ($shift)
        {
            $shifts = Shift::with('area')->where('uuid', $shift)->get();
        }
        else if ($area)
        {
            $shifts = Shift::with('area')->where('area_id', function ($query) use ($area)
            {
                $query->select('id')->from('areas')->where('uuid', $area);
            })->get();
        }
        else
        {
            $shifts = Shift::all();
        }

        $locks = [];
        $time_now = now();

        foreach ($shifts as $shift)
        {
            foreach ($period as $date)
            {
                if (Auth::user()->is_admin || $shift->area->responsible_person_id == Auth::user()->id)
                    $locks[$shift->uuid][$date->format('Y-m-d')] = ShiftArrangementLock::updateOrCreate(
                        [
                            'date' => $date->format('Y-m-d'),
                            'shift_id' => $shift->id,
                        ],
                        [
                            'is_locked' => $lock,
                        ]
                    )->is_locked;
                else
                    $locks[$shift->uuid][$date->format('Y-m-d')] = ShiftArrangementLock::firstOrNew(
                        [
                            'date' => $date->format('Y-m-d'),
                            'shift_id' => $shift->id,
                        ],
                        [
                            'is_locked' => $date < $time_now,
                        ]
                    )->is_locked;
            }
        }

        return $locks;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        abort(404);
    }
}
