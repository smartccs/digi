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
        'provider_id','user_id','current_provider_id',
        'service_type_id','status','cancelled_by',
        'paid','distance','s_latitude','d_latitude','s_longitude',
        'd_longitude','paid','s_address', 'd_address',
        'assigned_at','schedule_at','started_at',
        'finished_at'
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
     * ServiceType Model Linked
     */
    public function service_type()
    {
        return $this->belongsTo('App\ServiceType');
    }
    
    /**
     * UserPayment Model Linked
     */
    public function payment()
    {
        return $this->hasOne('App\UserPayment', 'request_id');
    }

    /**
     * UserRequestRating Model Linked
     */
    public function rating()
    {
        return $this->hasOne('App\UserRequestRating', 'request_id');
    }

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

    public function scopePendingRequest($query, $user_id)
    {
        return $query->where('user_id', $user_id)
                // ->where('later', 0) // Schedule - schedule_at != null
                ->whereNotIn('status' , ['CANCELLED', 'COMPLETED']);
    }

    public function scopeRequestHistory($query)
    {
        return $query->leftJoin('providers', 'user_requests.confirmed_provider', '=', 'providers.id')
                    ->leftJoin('users', 'user_requests.user_id', '=', 'users.id')
                    ->leftJoin('user_payments', 'user_requests.id', '=', 'user_payments.request_id')
                    ->select('user_requests.*','users.first_name as user_first_name', 'users.last_name as user_last_name',
                             'providers.first_name as provider_first_name', 'providers.last_name as provider_last_name', 
                             'users.id as user_id', 'providers.id as provider_id', 'user_payments.total as amount',
                            'user_payments.payment_mode as payment_mode', 'user_payments.status as payment_status')
                    ->orderBy('user_requests.created_at', 'desc');
    }

    public function scopeCheckRequestProvider($query, $request_id, $provider_id, $status)
    {
        return $query->where('id', '=', $request_id)
                    ->where('confirmed_provider', '=', $provider_id)
                    ->where('provider_status' , $status)
                    ->where('status', REQUEST_INPROGRESS);
    }

    public function scopeGetProviderHistory($query, $provider_id)
    {
        return $query->where('confirmed_provider', '=', $provider_id)
                    ->where('user_requests.status', '=', REQUEST_COMPLETED)
                    ->where('user_requests.provider_status', '=', PROVIDER_RATED)
                    ->leftJoin('request_payments', 'user_requests.id', '=', 'request_payments.request_id')
                    ->leftJoin('providers', 'providers.id', '=', 'user_requests.confirmed_provider')
                    ->leftJoin('users', 'users.id', '=', 'user_requests.user_id')
                    ->orderBy('request_start_time','desc')
                    ->select('user_requests.id', 'user_requests.request_type as request_type', 'request_start_time as date',
                        DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'), 'users.picture',
                        DB::raw('ROUND(request_payments.total) as total'));
    }


    public function scopeUserHistory($query, $user_id)
    {
        return $query->where('user_requests.user_id', '=', $user_id)
                    ->where('user_requests.status', '=', 'COMPLETED')
                    ->select('user_requests.*')
                    ->with('user','provider','rating','payment');
    }

    public function scopeUserRequestStatusCheck($query, $user_id, $check_status)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.user_rated',0)
                    ->whereNotIn('user_requests.status', $check_status)
                    ->select('user_requests.*')
                    ->with('user','provider','service_type','rating','payment');
    }


    public function scopeProviderUpcomingRequest($query, $provider_id)
    {
        return $query->where('user_requests.confirmed_provider' , $provider_id)
                    ->where('user_requests.later' , DEFAULT_TRUE)
                    ->where('user_requests.status' , REQUEST_INPROGRESS)
                    ->where('user_requests.provider_status' , '<',PROVIDER_STARTED)
                    ->leftJoin('users', 'users.id', '=', 'user_requests.user_id')
                    ->leftJoin('providers', 'providers.id', '=', 'user_requests.confirmed_provider')
                    ->leftJoin('service_types', 'service_types.id', '=', 'user_requests.request_type')
                    ->select('user_requests.id as request_id','user_requests.later','user_requests.requested_time', 'user_requests.request_type as request_type', 'service_types.name as service_type_name', 'request_start_time as request_start_time', 'user_requests.status','user_requests.confirmed_provider as provider_id', DB::raw('CONCAT(providers.first_name, " ", providers.last_name) as provider_name'),'providers.picture as provider_picture','user_requests.provider_status', 'user_requests.amount', DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'), 'users.picture as user_picture', 'users.id as user_id','user_requests.s_latitude', 'user_requests.s_longitude','user_requests.s_address');
    }

    public function scopeRequestDetails($query, $request_id)
    {
        return $query->where('user_requests.id' , $request_id)
                    ->leftJoin('providers' , 'user_requests.confirmed_provider','=' , 'providers.id')
                    ->leftJoin('users' , 'user_requests.user_id','=' , 'users.id')
                    ->leftJoin('user_ratings' , 'user_requests.id','=' , 'user_ratings.request_id')
                    ->leftJoin('request_payments' , 'user_requests.id','=' , 'request_payments.request_id')
                    ->leftJoin('cards','users.default_card','=' , 'cards.id')
                    ->leftJoin('service_types', 'service_types.id', '=', 'user_requests.request_type')
                    ->select('user_requests.request_start_time as request_start_time','user_requests.start_time as start_time','user_requests.requested_time as requested_time','user_requests.status as status' , 'user_requests.provider_status as provider_status' , 'user_requests.s_latitude as s_latitude' , 'user_requests.s_longitude as s_longitude' , 'user_requests.s_address as s_address' , 'providers.id as provider_id' , 'providers.picture as provider_picture',
                        DB::raw('CONCAT(providers.first_name, " ", providers.last_name) as provider_name'),'user_ratings.rating','user_ratings.comment',
                        DB::raw('ROUND(request_payments.base_price) as base_price'), 
                        DB::raw('ROUND(request_payments.tax_price) as tax_price'),
                        DB::raw('ROUND(request_payments.time_price) as time_price'), 
                        DB::raw('ROUND(request_payments.total) as total'),
                        'cards.id as card_id','cards.customer_id as customer_id','cards.card_token','cards.last_four','user_requests.id as request_id','user_requests.before_image','user_requests.after_image','user_requests.user_id as user_id','users.picture as user_picture','users.mobile as user_mobile','providers.mobile as provider_mobile','service_types.name as service_type_name',
                        DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'));
    }


}
