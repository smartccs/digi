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

    public function scopeCheckWaitingFilter($query, $request_id, $provider_id)
    {
        return $query->where('request_id', '=', $request_id)
                ->where('provider_id', '=', $provider_id)
                ->where('status', '=', REQUEST_WAITING);
    }

    public function scopeCheckOfferedFilter($query, $request_id, $provider_id)
    {
        return $query->where('request_id', '=', $request_id)
                ->where('provider_id', '=', $provider_id)
                ->where('status', '=', REQUEST_META_OFFERED);
    }

    public function scopeCheckOfferedFilter($query, $request_id, $provider_id)
    {
        return $query->where('request_id', '=', $request_id)
                ->where('provider_id', '=', $provider_id)
                ->where('status', '=', REQUEST_META_OFFERED);
    }

    public function scopeIncomingRequest($query, $user_id)
    {
        return $query->where('requests_meta.provider_id',$user_id)
                        ->where('requests_meta.status',REQUEST_META_OFFERED)
                        ->where('requests_meta.is_cancelled',0)
                        ->leftJoin('user_requests', 'user_requests.id', '=', 'requests_meta.request_id')
                        ->leftJoin('service_types', 'service_types.id', '=', 'requests.request_type')
                        ->leftJoin('users', 'users.id', '=', 'user_requests.user_id')
                        ->select('user_requests.id as request_id', 'user_requests.later', 'user_requests.later_status','user_requests.request_type as request_type', 'service_types.name as service_type_name', 'request_start_time as request_start_time', 'user_requests.start_time as start_time', 'user_requests.status', 'user_requests.provider_status', 'user_requests.amount', DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'), 'users.picture as user_picture', 'users.id as user_id','user_requests.s_latitude as latitude', 'user_requests.s_longitude as longitude');
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
