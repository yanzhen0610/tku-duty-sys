<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Area extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'area_name',
        'responsible_person_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'responsible_person_id',
        'responsible_person_eager',
        'shifts_eager',
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
        // 'responsible_person'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(Area $area) {
            $area->uuid = Str::uuid()->toString();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function getResponsiblePersonAttribute()
    {
        return $this->responsible_person_eager;
    }

    public function setResponsiblePersonAttribute($value)
    {
        if ($value instanceof User)
            $this->responsible_person_id = $value->id;
        else if (is_int($value))
            $this->responsible_person_id = $value;
        else if (is_string($value))
            $this->responsible_person_id = User::where('username', $value)->first()->id;
    }

    public function responsible_person_eager()
    {
        return $this->belongsTo('App\User', 'responsible_person_id', 'id');
    }

    public function shifts_eager()
    {
        return $this->hasMany('App\Shift', 'area_id', 'id');
    }

    public function getShiftsAttribute()
    {
        return $this->shifts_eager;
    }
}
