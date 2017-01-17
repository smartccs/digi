<?php

namespace App;

use DB;

use Illuminate\Database\Eloquent\Model;

class UserRequests extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_id','user_id','current_provider','confirmed_provider',
        'request_start_time', 'later','requested_time','request_meta_id',
        'request_type','provider_status','after_image', 'before_image',
        's_latitude','d_latitude','s_longitude','d_longitude','is_paid', 
        's_address', 'd_address','start_time','end_time','amount',
        'status','wallet_amount', 'is_promo_code', 'promo_code_id',
        'promo_code','offer_amount'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'created_at', 'updated_at'
    ];

    public function scopePendingRequest($query, $user_id)
    {
        return $query->where('user_id' , $user_id)
                ->where('later' , 0)
                ->whereNotIn('status' , [REQUEST_NO_PROVIDER_AVAILABLE,REQUEST_CANCELLED,REQUEST_TIME_EXCEED_CANCELLED,REQUEST_COMPLETED]);
    }

    public function scopeRequestStatusCheck($query, $user_id, $check_status)
    {
        return $query->where('user_requests.user_id', '=', $user_id)
                            ->whereNotIn('user_requests.status', $check_status)
                            ->leftJoin('users', 'users.id', '=', 'user_requests.user_id')
                            ->leftJoin('providers', 'providers.id', '=', 'user_requests.confirmed_provider')
                            ->leftJoin('service_types', 'service_types.id', '=', 'user_requests.request_type')
                            ->select(
                                'user_requests.id as request_id',
                                'user_requests.request_type as request_type',
                                'user_requests.later as later',
                                'user_requests.user_later_status as user_later_status',
                                'service_types.name as service_type_name',
                                'service_types.provider_name as service_provider_name',
                                'user_requests.after_image as after_image',
                                'user_requests.before_image as before_image',
                                'user_requests.end_time as end_time',
                                'request_start_time as request_start_time',
                                'user_requests.status','providers.id as provider_id',
                                DB::raw('CONCAT(providers.first_name, " ", providers.last_name) as provider_name'),
                                'providers.picture as provider_picture',
                                'providers.mobile as provider_mobile',
                                'user_requests.provider_status',
                                'user_requests.amount',
                                DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'),
                                'users.picture as user_picture',
                                'users.id as user_id',
                                'user_requests.s_latitude',
                                'user_requests.s_longitude',
                                'user_requests.s_address',
                                'user_requests.d_address',
                                'user_requests.promo_code_id',
                                'user_requests.promo_code',
                                'user_requests.offer_amount',
                                'user_requests.is_promo_code'
                            );
    }
}
