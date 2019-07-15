<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

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
        'isAdminEager',
        'isDisabledEager',
        'created_at',
        'updated_at',
        'deleted_at',
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
    public static $STATUS_RESET_PASSWORD_REQUESTED = 1;

    private static $GID_ADMIN = null;
    private static $GID_DISABLED = null;

    private function getAdminGID()
    {
        if (static::$GID_ADMIN == null)
        {
            static::$GID_ADMIN = Group::where('group_name', 'admin')->first(['id'])->id;
        }
        return static::$GID_ADMIN;
    }

    private static function getDisabledGID()
    {
        if (static::$GID_DISABLED == null)
        {
            static::$GID_DISABLED = Group::where('group_name', 'disabled')->first(['id'])->id;
        }
        return static::$GID_DISABLED;
    }

    public function getIsAdminAttribute()
    {
        return $this->username == 'admin' || count($this->isAdminEager) >= 1;
    }

    public function setIsAdminAttribute($value)
    {
        if ($value === true) $this->entitleAdmin();
        else if ($value === false) $this->revokeAdmin();
    }

    public function getIsDisabledAttribute()
    {
        return $this->username != 'admin' && count($this->isDisabledEager) >= 1;
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

    public function isAdminEager()
    {
        return $this->groups()->WherePivot('group_id', '=', static::getAdminGID());
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

    public function isDisabledEager()
    {
        return $this->groups()->WherePivot('group_id', '=', static::getDisabledGID());
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
