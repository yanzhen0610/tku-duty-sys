<?php

namespace App\Http\Controllers;

use App\{Area, Shift, User, ShiftArrangement};
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Validator;

class ShiftsArrangementsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private static $DEFAULT_DURATION_DAYS = 30;
    private static $START_OF_WEEK = Carbon::SUNDAY;
    private static $END_OF_WEEK = Carbon::SATURDAY;

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
            'from_date',
            'to_date',
            'area' => ['exists:areas,uuid'],
            'shift' => ['exists:shifts,uuid'],
        ]);

        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        try
        {
            $from_date = new Carbon($request->input('from_date'));
            $to_date = new Carbon($request->input('to_date', $from_date));
            $to_date->addDays(ShiftsArrangementsController::$DEFAULT_DURATION_DAYS);
        } catch (Exception $e)
        {
            abort(400);
        }
        $from_date = $from_date->startOfWeek(ShiftsArrangementsController::$START_OF_WEEK);
        $to_date = $to_date->endOfWeek(ShiftsArrangementsController::$END_OF_WEEK);
        $area = $request->input('area');
        $shift = $request->input('shift');

        $query = ShiftArrangement::with(['shift', 'onDutyStaff']);
        $query = $query->whereBetween('date', [$from_date, $to_date]);
        if ($area)
        {
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
        if ($shift)
        {
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

        return $query->get();
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
        $validator = Validator::make($request->all(), [
            'shift' => ['required', 'exists:shifts,uuid'],
            'on_duty_staff' => ['required', 'exists:users,username'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        $on_duty_staff = $request->input('on_duty_staff');
        if (!Auth::user()->is_admin && 
                $on_duty_staff != Auth::user()->username)
            return response(null, 403);

        $shift_id = Shift::where('uuid', $request->input('shift'))
            ->first()->id;
        $on_duty_staff_id = User::where('username', $on_duty_staff)
            ->first()->id;

        try
        {
            $arrangement = ShiftArrangement::create([
                'shift_id' => $shift_id,
                'on_duty_staff_id' => $on_duty_staff_id,
                'date' => $request->input('date'),
            ])->load('shift', 'onDutyStaff');
        } catch (QueryException $e)
        {
            return response(null, 400);
        }

        return $arrangement;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ShiftArrangement  $shiftArrangement
     * @return \Illuminate\Http\Response
     */
    public function show(ShiftArrangement $shiftsArrangement)
    {
        //
        return $shiftsArrangement;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ShiftArrangement  $shiftArrangement
     * @return \Illuminate\Http\Response
     */
    public function edit(ShiftArrangement $shiftArrangement)
    {
        //
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ShiftArrangement  $shiftArrangement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShiftArrangement $shiftArrangement)
    {
        //
        abort(501);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ShiftArrangement  $shiftArrangement
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShiftArrangement $shiftsArrangement)
    {
        //
        if (!Auth::user()->is_admin && 
                $shiftsArrangement->on_duty_staff->username !=
                    Auth::user()->username)
            abort(403);
        if ($shiftsArrangement->delete())
            return response(null, 204);
        return response(null, 400);
    }
}
