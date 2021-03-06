<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['index', 'show']]);
    }

    private static $usersFields = [
        [
            'name' => 'username',
            'type' => 'text',
        ],
        [
            'name' => 'display_name',
            'type' => 'text'
        ],
        [
            'name' => 'mobile_ext',
            'type' => 'text'
        ],
        [
            'name' => 'is_disabled',
            'type' => 'checkbox'
        ],
        [
            'name' => 'is_admin',
            'type' => 'checkbox'
        ],
    ];

    static function usersFields()
    {
        if (Auth::user()->is_admin)
            return array_merge(static::$usersFields, [
                [
                    'name' => 'reset_password',
                    'type' => 'button-popup-window',
                    'url_getter' => function (User $user, $key = null) {
                        return $user->status == User::$STATUS_RESET_PASSWORD_REQUESTED
                            ? route('admin.changeUserPassword', $user->username) : null;
                    },
                ]
            ]);
        return static::$usersFields;
    }

    public static function userFilterOutFields(User $user)
    {
        $fields = $user->toArray();
        foreach (static::usersFields() as $key => $value)
            if ($value['type'] != 'button-popup-window')
                $fields[$value['name']] = $user->{$value['name']};
        if (Auth::user()->is_admin)
        {
            $fields['show_url'] = route('users.show', $user->username);
            $fields['update_url'] = route('users.update', $user->username);
            $fields['destroy_url'] = route('users.destroy', $user->username);
            $fields['reset_password'] = $user->status == User::$STATUS_RESET_PASSWORD_REQUESTED
                ? route('admin.changeUserPassword', $user->username) : null;
        }
        $fields['username'] = $user->username;
        return $fields;
    }

    public static function getUsersData()
    {
        $users_data = [
            'fields' => static::usersFields(),
            'rows' => User::with(['is_admin_eager', 'is_disabled_eager'])
                ->get()->map([static::class, 'userFilterOutFields'])
                ->sortBy('is_disabled')->values(),
            'editable' => Auth::user()->is_admin,
            'destroyable' => Auth::user()->is_admin,
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
        return static::getUsersData();
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
            'username' => [
                'required',
                'min:1',
                'max:255',
                Rule::unique('users', 'username')->whereNull('deleted_at'),
                'regex:/^[^\/]+$/u'
            ],
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
        return $this->userFilterOutFields($user);
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  \App\User  $user
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit(User $user)
    // {
    //     abort(404);
    // }

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
        $user->delete();
    }

    public function resetPassword(User $user) {
        $user->password = Hash::make($user->username);
        $user->status = User::$STATUS_NORMAL;
        $user->save();
        return $this->userFilterOutFields($user);
    }
}
