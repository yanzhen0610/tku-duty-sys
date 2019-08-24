<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

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
        'area_eager',
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
        return $this->area_eager;
    }

    public function setAreaAttribute($value)
    {
        if ($value instanceof Area)
            $this->area_id = $value->id;
        else if (is_int($value))
            $this->area_id = $value;
        else if (is_string($value))
            $this->area_id = Area::where('uuid', $value)->first()->id;
    }

    public function area_eager()
    {
        return $this->belongsTo('App\Area', 'area_id', 'id');
    }
}
