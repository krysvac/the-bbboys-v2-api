<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Weeb_answers extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
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
        'ip_address'
    ];

    public function scopeByUserId($query, $user_id)
    {
        User::findOrFail($user_id);

        $start = Carbon::createFromTimestamp(strtotime('monday this week'))->toDateTimeString();

        return $query->where([
            ["weeb_answers.user_id", "=", $user_id],
            ["weeb_answers.timestamp", ">=", $start]
        ])
            ->join('weeb_choices', 'weeb_answers.choice_id', '=', 'weeb_choices.id')
            ->orderBy('weeb_answers.timestamp', 'desc');
    }

    public function scopeGetAll($query)
    {
        $start = Carbon::createFromTimestamp(strtotime('monday this week'))->toDateTimeString();

        return $query->where([
            ["weeb_answers.timestamp", ">=", $start]
        ])
            ->join('weeb_choices', 'weeb_answers.choice_id', '=', 'weeb_choices.id')
            ->orderBy('weeb_answers.timestamp', 'desc');
    }
}
