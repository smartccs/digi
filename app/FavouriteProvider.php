<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FavouriteProvider extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','provider_id','status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function scope($query, $user_id, $provider_id)
    {
        return $query->where('provider_id', $provider_id)->where('user_id', $user_id);
    }
}
