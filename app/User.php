<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'display_name',
        'mobile_ext',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'email_verified_at',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'is_admin',
        // 'is_disabled',
    ];

    public static $STATUS_NORMAL = 0;
    public static $STATUS_RESET_PASSWORD_REQUESTED = 5;

    public function getIsAdminAttribute()
    {
        return $this->isAdmin();
    }

    public function setIsAdminAttribute($value)
    {
        if ($value === true) $this->entitleAdmin();
        else if ($value === false) $this->revokeAdmin();
    }

    public function getIsDisabledAttribute()
    {
        return $this->isDisabled();
    }

    public function setIsDisabledAttribute($value)
    {
        if ($value === true) $this->disable();
        else if ($value === false) $this->enable();
    }

    public function groups()
    {
        return $this->belongsToMany('\App\Group', 'users_groups', 'user_id', 'group_id');
    }

    public function getRouteKeyName()
    {
        return 'username';
    }

    public static function getByUsername(String $username)
    {
        return static::where('username', $username)->first();
    }

    public function isAdmin()
    {
        return $this->username == 'admin' || $this->groups->contains(Group::where('group_name', 'admin')->first());
    }

    public function entitleAdmin()
    {
        if ($this->username == 'admin') return;
        try
        {
            $this->groups()->save(Group::where('group_name', 'admin')->first());
        } catch (QueryException $e) {}
    }

    public function revokeAdmin()
    {
        if ($this->username == 'admin') return;
        try
        {
            $this->groups()->detach(Group::where('group_name', 'admin')->first());
        } catch (QueryException $e) {}
    }

    public function isDisabled()
    {
        return $this->username != 'admin' && $this->groups->contains(Group::where('group_name', 'disabled')->first());
    }

    public function disable()
    {
        if ($this->username == 'admin') return;
        try
        {
            $this->groups()->save(Group::where('group_name', 'disabled')->first());
        } catch (QueryException $e) {}
    }

    public function enable()
    {
        if ($this->username == 'admin') return;
        try
        {
            $this->groups()->detach(Group::where('group_name', 'disabled')->first());
        } catch (QueryException $e) {}
    }

}
