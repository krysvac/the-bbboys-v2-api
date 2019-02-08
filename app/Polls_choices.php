<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Polls_choices extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'poll_id',
        'value'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function scopeByPollId($query, $poll_id)
    {
        Polls::findOrFail($poll_id);

        return $query->where("poll_id", "=", $poll_id);
    }
}
