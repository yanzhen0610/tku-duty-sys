<?php

namespace App\Http\Controllers;

use App\{Area, Shift};
use App\EditTable\EditTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

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

    static function getShiftsData()
    {
        return new EditTable(
            Shift::with([
                'area_eager' => function ($query) { $query->withTrashed(); }
            ])->get(),
            static::shiftsFields(),
            Auth::user()->is_admin,
            Auth::user()->is_admin,
            'shift_name',
            Auth::user()->is_admin ? 'shifts.store' : null,
            Auth::user()->is_admin ? 'shifts.update' : null,
            Auth::user()->is_admin ? 'shifts.destroy' : null
        );
    }

    static function singleShift(Shift $shift)
    {
        return EditTable::singleRow(
            $shift,
            static::shiftsFields(),
            'shift_name',
            Auth::user()->is_admin ? 'shifts.update' : null,
            Auth::user()->is_admin ? 'shifts.destroy' : null
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return static::getShiftsData();
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
        $shift->load('area_eager');

        return static::singleShift($shift);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function show(Shift $shift)
    {
        return static::singleShift($shift);
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  \App\Shift  $shift
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit(Shift $shift)
    // {
    //     abort(404);
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shift $shift)
    {
        $validator = Validator::make($request->all(), [
            'shift_name' => ['min:1', 'max:255'],
            'area.selected' => ['exists:areas,uuid'],
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
        if (array_key_exists('area', $fields))
            $fields['area'] = $fields['area']['selected'];

        foreach ($fields as $key => $value)
            $shift->$key = $value;
        $shift->save();

        return static::singleShift($shift);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shift $shift)
    {
        $shift->delete();
    }
}
