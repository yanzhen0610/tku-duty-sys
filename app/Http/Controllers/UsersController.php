<?php

namespace App\Http\Controllers;

use App\User;
use App\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['index', 'show']]);
    }

    private static $usersFields = [
        'display_name' => 'text',
        'mobile_ext' => 'text',
        'is_disabled' => 'checkbox',
        'is_admin' => 'checkbox',
    ];

    static function usersFields() {
        if (Auth::user()->is_admin)
            return array_merge(static::$usersFields, ['reset_password' => 'button-link']);
        return static::$usersFields;
    }

    public static function userFilterOutFields(User $user) {
        $arr = array();
        foreach (static::usersFields() as $key => $value)
            if ($value != 'button-link')
                $arr[$key] = $user->$key;
        if (Auth::user()->is_admin) {
            $arr['update_url'] = route('users.update', $user['username']);
            $arr['reset_password'] = [
                'method' => 'DELETE',
                'url' => $user->status == User::$STATUS_RESET_PASSWORD_REQUESTED
                    ? route('users.password.reset', $user['username']) : null,
            ];
        }
        $arr['key'] = $user->username;
        return $arr;
    }

    public static function getUsersData() {
        $users_data = [
            'fields' => static::usersFields(),
            'rows' => User::all()->map([static::class, 'userFilterOutFields']),
            'editable' => Auth::user()->is_admin,
            'primary_key' => 'username',
        ];
        if (Auth::user()->is_admin)
            $users_data['create_url'] = route('users.store');
        return $users_data;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users_data = static::getUsersData();
        $groups_data = GroupsController::getGroupsData();
        return view('users.index', compact(['users_data', 'groups_data']));
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
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'min:1', 'max:255', 'unique:users', 'regex:/^[^\/]+$/u'],
            'display_name' => ['max:255'],
            'mobile_ext' => ['max:255'],
        ]);
        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        $input = $request->only(['username', 'display_name', 'mobile_ext']);
        $input['password'] = Hash::make($input['username']);
        $user = User::create($input);
        foreach ($request->only(['is_disabled', 'is_admin']) as $key => $value)
            $user->$key = $value;

        return static::userFilterOutFields($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
        return $this->userFilterOutFields($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'display_name' => ['max:255'],
            'mobile_ext' => ['max:255'],
        ]);
        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        foreach ($request->only(['display_name', 'mobile_ext', 'is_disabled', 'is_admin']) as $key => $value)
            $user->$key = $value;
        $user->save();

        return static::userFilterOutFields($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
        abort(404);
    }

    public function resetPassword(User $user) {
        $user->password = Hash::make($user->username);
        $user->status = User::$STATUS_NORMAL;
        $user->save();
        return $this->userFilterOutFields($user);
    }
}
