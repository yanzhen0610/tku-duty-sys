<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'shift_name',
        'area_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'area_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'area'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(Shift $shift) {
            $shift->uuid = Str::uuid()->toString();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function getAreaAttribute()
    {
        return $this->area()->getResults();
    }

    public function setAreaAttribute($value)
    {
        if ($value instanceof Area)
        {
            $this->area_id = $value->id;
            $this->save();
        }
        else if (is_int($value))
        {
            $this->area_id = $value;
            $this->save();
        }
        else if (is_string($value))
        {
            $this->area_id = Area::where('uuid', $value)->first()->id;
            $this->save();
        }
    }

    public function area()
    {
        return $this->belongsTo('App\Area', 'area_id', 'id');
    }
}
