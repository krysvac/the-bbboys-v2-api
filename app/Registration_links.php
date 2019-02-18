<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Registration_links extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'timestamp'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id'
    ];

    public function scopeByToken($query, $token)
    {
        return $query->where([
            ["token", "=", $token],
            ["used", "=", 0],
        ]);
    }
}
