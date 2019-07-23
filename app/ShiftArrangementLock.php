<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftArrangementLock extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shift_arrangement_locks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'shift_id',
        'is_locked',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'shift_id',
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
        'is_locked' => 'boolean',
    ];

    public function shift()
    {
        return $this->belongsTo('App\Shift', 'shift_id', 'id');
    }

}
