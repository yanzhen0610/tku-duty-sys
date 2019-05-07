<?php

namespace App\Http\Controllers;

use App\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Validator;

class GroupsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['index', 'show']]);
    }

    static function groupsFields()
    {
        if (Auth::user()->is_admin)
            return ['group_name' => ['type' => 'text']];
        return [];
    }

    public static function groupFilterOutFields(Group $group)
    {
        $fields = array();
        foreach (static::groupsFields() as $key => $value)
            if ($value != 'button-link')
                $fields[$key] = $group->$key;
        if (Auth::user()->is_admin)
        {
            $fields['update_url'] = route('groups.update', $group->uuid);
            $fields['destroy_url'] = route('groups.destroy', $group->uuid);
        }
        $fields['key'] = $group->group_name;
        return $fields;
    }

    static function getGroupsData()
    {
        $groups_data = [
            'fields' => GroupsController::groupsFields(),
            'rows' => Group::whereNotIn('group_name', ['admin', 'disabled'])
                ->get()->map([GroupsController::class, 'groupFilterOutFields']),
            'primary_key' => 'group_name',
        ];
        if (Auth::user()->is_admin)
        {
            $groups_data['editable'] = true;
            $groups_data['create_url'] = route('groups.store');
            $groups_data['destroyable'] = true;
        }
        return $groups_data;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return static::getGroupsData();
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
            'group_name' => ['required', 'min:1', 'max:255'],
        ]);
        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        $group = Group::create($request->only(['group_name']));

        return static::groupFilterOutFields($group);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        //
        return static::groupFilterOutFields($group);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function edit(Group $group)
    {
        //
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group $group)
    {
        //
        $validator = Validator::make($request->all(), [
            'group_name' => ['required', 'min:1', 'max:255'],
        ]);
        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        foreach ($request->only(['group_name']) as $key => $value)
            $group->$key = $value;
        $group->save();

        return static::groupFilterOutFields($group);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        //
        $group->delete();
    }
}
