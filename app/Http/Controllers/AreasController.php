<?php

namespace App\Http\Controllers;

use App\{Area, User};
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class AreasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['index', 'show']]);
    }

    static function listAreas()
    {
        return Area::all()->map(function(Area $area)
        {
            return [
                'key' => $area->uuid,
                'display_name' => $area->area_name,
            ];
        });
    }

    static function areasFields()
    {
        $base = [
            'responsible_person' => [
                'type' => 'dropdown',
                'default' => UsersController::listUsers(),
            ],
        ];
        if (Auth::user()->is_admin)
            return array_merge(['area_name' => ['type' => 'text']], $base);
        return $base;
    }

    static function areaFilterOutFields(Area $area)
    {
        $fields = array();
        foreach ($area->toArray() as $key => $value) {
            if ($key == 'responsible_person')
                $fields[$key] = [
                    'selected' => $value['username'],
                ];
            else
                $fields[$key] = $value;
        }
        if (Auth::user()->is_admin)
        {
            $fields['update_url'] = route('areas.update', $area->uuid);
            $fields['destroy_url'] = route('areas.destroy', $area->uuid);
        }
        $fields['key'] = $area->area_name;
        return $fields;
    }

    static function getAreasData()
    {
        $areas_data = [
            'fields' => AreasController::areasFields(),
            'rows' => Area::with('responsiblePerson')->get()
                ->map([AreasController::class, 'areaFilterOutFields']),
            'primary_key' => 'area_name',
        ];
        if (Auth::user()->is_admin)
        {
            $areas_data['editable'] = true;
            $areas_data['create_url'] = route('areas.store');
            $areas_data['destroyable'] = true;
        }
        return $areas_data;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return static::getAreasData();
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
            'area_name' => ['required', 'min:1', 'max:255'],
            'responsible_person.selected' => ['required', 'exists:users,username'],
        ])->setAttributeNames([
            'responsible_person.selected' =>
                Lang::get('validation.attributes.responsible_person'),
        ]);
        if ($validator->fails())
            return response()->json(array_rename_key(
                $validator->messages()->toArray(),
                'responsible_person.selected',
                'responsible_person'
            ), 400);

        $fields = $request->only(['area_name', 'responsible_person']);
        $fields['responsible_person'] = $fields['responsible_person']['selected'];
        $fields['responsible_person_id'] = User::where(
            'username',
            $fields['responsible_person']
        )->first()->id;

        $area = Area::create($fields);
        $area->load('responsiblePerson');

        return static::areaFilterOutFields($area);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function show(Area $area)
    {
        //
        return static::areaFilterOutFields($area);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function edit(Area $area)
    {
        //
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Area $area)
    {
        //
        $validator = Validator::make($request->all(), [
            'area_name' => ['required', 'min:1', 'max:255'],
            'responsible_person.selected' => ['required', 'exists:users,username'],
        ])->setAttributeNames([
            'responsible_person.selected' =>
                Lang::get('validation.attributes.responsible_person'),
        ]);
        if ($validator->fails())
            return response()->json(array_rename_key(
                $validator->messages()->toArray(),
                'responsible_person.selected',
                'responsible_person'
            ), 400);

        $fields = $request->only(['area_name', 'responsible_person']);
        $fields['responsible_person'] = $fields['responsible_person']['selected'];

        foreach ($fields as $key => $value)
            $area->$key = $value;
        $area->save();

        return static::areaFilterOutFields($area);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function destroy(Area $area)
    {
        //
        try
        {
            $area->delete();
        }
        catch (QueryException $e)
        {
            return response(null, 400);
        }
    }
}
