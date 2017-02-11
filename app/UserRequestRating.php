<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRequestRating extends Model
{
    /**
     * The user who created the request.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * The provider assigned to the request.
     */
    public function provider()
    {
        return $this->belongsTo('App\Provider');
    }
}
