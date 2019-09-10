<?php

namespace App\Http\Controllers;

use App\{Area, User};
use App\EditTable\EditTable;
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
                'default' => User::withTrashed()
                    ->whereNull('deleted_at')
                    ->orWhereIn('id', function ($query) {
                        $query->select('responsible_person_id')->from((new Area())->getTable())
                            ->whereNull('deleted_at');
                    })->get()->map(function (User $user) {
                        return [
                            'key' => $user->username,
                            'display_name' => $user->display_name,
                        ];
                    }),
            ],
        ];
        if (Auth::user()->is_admin)
            return array_merge(['area_name' => ['type' => 'text']], $base);
        return $base;
    }

    static function getAreasData()
    {
        return new EditTable(
            Area::with(['responsible_person_eager' => function ($query) {
                $query->withTrashed();
            }])->get(),
            static::areasFields(),
            Auth::user()->is_admin,
            Auth::user()->is_admin,
            'area_name',
            Auth::user()->is_admin ? 'areas.store' : null,
            Auth::user()->is_admin ? 'areas.update' : null,
            Auth::user()->is_admin ? 'areas.destroy' : null
        );
    }

    static function singleArea(Area $area)
    {
        return EditTable::singleRow(
            $area,
            static::areasFields(),
            'area_name',
            Auth::user()->is_admin ? 'areas.update' : null,
            Auth::user()->is_admin ? 'areas.destroy' : null
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return static::getAreasData();
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
        $area->load('responsible_person_eager');

        return static::singleArea($area);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function show(Area $area)
    {
        return static::singleArea($area);
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  \App\Area  $area
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit(Area $area)
    // {
    //     abort(404);
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Area $area)
    {
        $validator = Validator::make($request->all(), [
            'area_name' => ['min:1', 'max:255'],
            'responsible_person.selected' => ['exists:users,username'],
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

        $fields = $request->only(['area_name', 'responsible_person.selected']);
        if (array_key_exists('responsible_person', $fields))
            $fields['responsible_person'] =
                $fields['responsible_person']['selected'];

        foreach ($fields as $key => $value)
            $area->$key = $value;
        $area->save();

        return static::singleArea($area);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function destroy(Area $area)
    {
        $area->delete();
    }
}
