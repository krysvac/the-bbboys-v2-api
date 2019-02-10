<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Polls_answers extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'poll_id',
        'choice_id',
        'ip_address'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'ip_address',
        'timestamp'
    ];

    public function scopeByPollId($query, $poll_id)
    {
        Polls::findOrFail($poll_id);

        $start = Carbon::createFromTimestamp(strtotime('today midnight'))->toDateTimeString();
        $end = Carbon::createFromTimestamp(strtotime('today midnight +23 hours 59 minutes 59 seconds'))->toDateTimeString();

        return $query->where([
            ["poll_id", "=", $poll_id],
            ["timestamp", ">=", $start],
            ["timestamp", "<=", $end]
        ]);
    }

    public function scopeByUserId($query, $user_id)
    {
        User::findOrFail($user_id);

        $start = Carbon::createFromTimestamp(strtotime('today midnight'))->toDateTimeString();
        $end = Carbon::createFromTimestamp(strtotime('today midnight +23 hours 59 minutes 59 seconds'))->toDateTimeString();

        return $query->where([
            ["user_id", "=", $user_id],
            ["timestamp", ">=", $start],
            ["timestamp", "<=", $end]
        ]);
    }
}
