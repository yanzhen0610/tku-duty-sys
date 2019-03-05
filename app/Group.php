<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_name',
    ];

    protected $hidden = [
        'id',
    ];

    public function getRouteKeyName()
    {
        return 'group_name';
    }

    public function users() {
        return $this->belongsToMany('\App\User', 'users_groups', 'group_id', 'user_id');
    }
}
