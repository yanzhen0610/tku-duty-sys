<?php

namespace App\Http\Controllers;

use App\{Area, Shift};
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Validator;

class ShiftsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['index', 'show']]);
    }

    static function shiftsFields()
    {
        $base = [
            'area' => [
                'type' => 'dropdown',
                'default' => AreasController::listAreas(),
            ],
            'working_time' => [
                'type' => 'text',
            ],
            'working_hours' => [
                'type' => 'text',
            ],
        ];
        if (Auth::user()->is_admin)
            return array_merge(
                ['shift_name' => ['type' => 'text']],
                $base
            );
        return $base;
    }

    static function shiftFilterOutFields(Shift $shift)
    {
        $fields = array();
        foreach (static::shiftsFields() as $key => $value)
            if ($key == 'area')
                $fields[$key] = [
                    'selected' => $shift->$key->uuid,
                ];
            else
                $fields[$key] = $shift->$key;
        if (Auth::user()->is_admin)
        {
            $fields['update_url'] = route('shifts.update', $shift->uuid);
            $fields['destroy_url'] = route('shifts.destroy', $shift->uuid);
        }
        $fields['key'] = $shift->shift_name;
        return $fields;
    }

    static function getShiftsData()
    {
        $shifts_data = [
            'fields' => ShiftsController::shiftsFields(),
            'rows' => Shift::with('area')->get()
                ->map([ShiftsController::class, 'shiftFilterOutFields']),
            'primary_key' => 'shift_name',
        ];
        if (Auth::user()->is_admin)
        {
            $shifts_data['editable'] = true;
            $shifts_data['create_url'] = route('shifts.store');
            $shifts_data['destroyable'] = true;
        }
        return $shifts_data;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return static::getShiftsData();
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
            'shift_name' => ['required', 'min:1', 'max:255'],
            'area.selected' => ['required', 'exists:areas,uuid'],
        ])->setAttributeNames([
            'area.selected' => 
                Lang::get('validation.attributes.area_name'),
        ]);

        if ($validator->fails())
            return response()->json(array_rename_key(
                $validator->messages()->toArray(),
                'area.selected',
                'area'
            ), 400);

        $fields = $request->only([
            'shift_name',
            'area',
            'working_time',
            'working_hours',
        ]);
        $fields['area'] = $fields['area']['selected'];
        $fields['area_id'] = Area::where('uuid', $fields['area'])->first()->id;

        $shift = Shift::create($fields);

        return static::shiftFilterOutFields($shift);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function show(Shift $shift)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function edit(Shift $shift)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shift $shift)
    {
        //
        $validator = Validator::make($request->all(), [
            'shift_name' => ['required', 'min:1', 'max:255'],
            'area.selected' => ['required', 'exists:areas,uuid'],
        ])->setAttributeNames([
            'area.selected' => 
                Lang::get('validation.attributes.area_name'),
        ]);

        if ($validator->fails())
            return response()->json(array_rename_key(
                $validator->messages()->toArray(),
                'area.selected',
                'area'
            ), 400);

        $fields = $request->only([
            'shift_name',
            'area.selected',
            'working_time',
            'working_hours',
        ]);
        $fields['area'] = $fields['area']['selected'];

        foreach ($fields as $key => $value)
            $shift->$key = $value;
        $shift->save();

        return static::shiftFilterOutFields($shift);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shift $shift)
    {
        //
        try
        {
            $shift->delete();
        }
        catch (QueryException $e)
        {
            return response(null, 400);
        }
    }
}
