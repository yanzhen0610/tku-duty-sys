<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Area extends Model
{
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
        'created_at',
        'updated_at',
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
        return $this->responsiblePerson()->getResults();
    }

    public function setResponsiblePersonAttribute($value)
    {
        if ($value instanceof User) {
            $this->responsible_person_id = $value->id;
            $this->save();
        }
        else if (is_int($value))
        {
            $this->responsible_person_id = $value;
            $this->save();
        }
        else if (is_string($value))
        {
            $this->responsible_person_id = User::where('username', $value)->first()->id;
            $this->save();
        }
    }

    public function responsiblePerson()
    {
        return $this->belongsTo('App\User', 'responsible_person_id', 'id');
    }
}
