<?php

namespace App;

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
}
