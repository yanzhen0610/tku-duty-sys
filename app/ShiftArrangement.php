<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ShiftArrangement extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shifts_arrangements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'shift_id',
        'on_duty_staff_id',
        'date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'shift_id',
        'shift_eager',
        'on_duty_staff_id',
        'on_duty_staff_eager',
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
        // 'shift',
        // 'on_duty_staff',
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

        static::creating(function(ShiftArrangement $shiftArrangement) {
            $shiftArrangement->uuid = Str::uuid()->toString();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function shift_eager()
    {
        return $this->belongsTo('App\Shift', 'shift_id', 'id');
    }

    public function on_duty_staff_eager()
    {
        return $this->belongsTo('App\User', 'on_duty_staff_id', 'id');
    }

    public function getShiftAttribute()
    {
        return $this->shift_eager;
    }

    public function getOnDutyStaffAttribute()
    {
        return $this->on_duty_staff_eager;
    }

    public function setShiftAttribute($value)
    {
        if ($value instanceof Shift)
            $this->shift_id = $value->id;
        else if (is_int($value))
            $this->shift_id = $value;
        else if (is_string($value))
            $this->shift_id = Shift::where('uuid', $value)->first()->id;
    }

    public function setOnDutyStaffAttribute($value)
    {
        if ($value instanceof User)
            $this->on_duty_staff_id = $value->id;
        else if (is_int($value))
            $this->on_duty_staff_id = $value;
        else if (is_string($value))
            $this->on_duty_staff_id = User::where('uuid', $value)->first()->id;
    }
}
