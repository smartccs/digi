<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderRating extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','provider_id','rating','comment','status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function scopeAverage($query, $user_id)
    {
        return $query->where('user_id', $user_id)->avg('rating');
    }
}
