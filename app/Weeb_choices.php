<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Weeb_choices extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'value',
        'name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function scopeGetCurrentChoices($query)
    {
        $start = Carbon::createFromTimestamp(strtotime('monday this week'))->toDateTimeString();

        return $query->where([
            ["timestamp", ">=", $start]
        ]);
    }

    public function scopeGetByValue($query, $value)
    {
        $start = Carbon::createFromTimestamp(strtotime('monday this week'))->toDateTimeString();

        return $query->where([
            ["value", "=", $value],
            ["timestamp", ">=", $start]
        ]);
    }
}
