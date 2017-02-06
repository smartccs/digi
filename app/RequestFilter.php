<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestFilter extends Model
{
   	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_id','provider_id','status','service_id','is_cancelled'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'created_at', 'updated_at'
    ];

    /**
     * The services that belong to the user.
     */
    public function request()
    {
        return $this->belongsTo('App\UserRequests');
    }

    public function scopeIncomingRequest($query, $provider_id)
    {
        return $query->with(['request.user','request.payment', 'request' => function ($query) use ($provider_id) {
            $query->where('current_provider_id', $provider_id);
        }])->where('provider_id', $provider_id);
    }


}
