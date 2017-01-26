<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'promo_code','offer','is_valid'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
