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


    public function scopeCheckWaitingFilter($query, $request_id, $provider_id)
    {
        return $query->where('request_id', '=', $request_id)
                ->where('provider_id', '=', $provider_id)
                ->where('status', 0);
    }

    public function scopeCheckOfferedFilter($query, $request_id, $provider_id)
    {
        return $query->where('request_id', '=', $request_id)
                ->where('provider_id', '=', $provider_id)
                ->where('status', '=', REQUEST_META_OFFERED);
    }

    public function scopeIncomingRequest($query, $provider_id)
    {
        return $query->with(['request.user', 'request' => function ($query) use ($provider_id) {
            $query->where('current_provider_id', $provider_id);
        }])->where('provider_id', $provider_id);
    }

    public function scopeFindNextProvider($query, $request_id)
    {
        return $query->where('request_id', '=', $request_id)->where('status', REQUEST_META_NONE)
                    ->leftJoin('providers', 'providers.id', '=', 'request_filters.provider_id')
                    ->where('providers.is_activated',DEFAULT_TRUE)
                    ->where('providers.is_approved',DEFAULT_TRUE)
                    ->where('providers.is_available',DEFAULT_TRUE)
                    ->where('providers.waiting_to_respond',WAITING_TO_RESPOND_NORMAL)
                    ->select('request_filters.id','request_filters.status','request_filters.provider_id')
                    ->orderBy('request_filters.created_at');
    }


}
