<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShiftArrangementChange extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shift_arrangement_changes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'date',
        'shift_id',
        'on_duty_staff_id',
        'changer_id',
        'is_locked',
        'is_up',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'shift_id',
        'on_duty_staff_id',
        'changer_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(ShiftArrangementChange $shiftArrangementChange) {
            $shiftArrangementChange->uuid = Str::uuid()->toString();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function shift()
    {
        return $this->belongsTo('App\Shift', 'shift_id', 'id')->withTrashed();
    }

    public function onDutyStaff()
    {
        return $this->belongsTo('App\User', 'on_duty_staff_id', 'id')->withTrashed();
    }

    public function changer()
    {
        return $this->belongsTo('App\User', 'changer_id', 'id')->withTrashed();
    }
    
}
